<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\UserAnalyticsService;
use App\Services\DocumentAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    private UserAnalyticsService $analyticsService;
    private DocumentAnalysisService $documentAnalysisService;

    public function __construct(
        UserAnalyticsService $analyticsService,
        DocumentAnalysisService $documentAnalysisService
    ) {
        $this->analyticsService = $analyticsService;
        $this->documentAnalysisService = $documentAnalysisService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,txt,xls,xlsx,png,jpg,jpeg|max:20480', // 20 Mo max
            'category' => 'nullable|string|in:business_plan,financial_docs,legal_docs,marketing,other'
        ]);

        $user = Auth::user();
        
        // Vérifier le nombre de documents existants (max 10)
        $existingDocuments = Document::where('user_id', $user->id)->count();
        
        if ($existingDocuments >= 10) {
            return response()->json([
                'error' => 'Vous avez atteint la limite de 10 documents'
            ], 422);
        }
        
        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Stocker le fichier
        $filePath = $file->storeAs('documents/' . $user->id, $filename, 'private');
        
        // Créer l'enregistrement Document
        $document = Document::create([
            'user_id' => $user->id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'category' => $request->get('category', 'other'),
            'is_processed' => false
        ]);

        // Track upload in analytics
        $fileInfo = [
            'filename' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'size_mb' => round($file->getSize() / 1024 / 1024, 2),
            'category' => $request->get('category', 'other'),
            'path' => $filePath
        ];
        $this->analyticsService->trackDataSourceUpload($user, $fileInfo);

        // Lancer l'analyse en arrière-plan (ou synchrone pour demo)
        try {
            $analysisResult = $this->documentAnalysisService->analyzeDocument($document);
            
            return response()->json([
                'success' => true,
                'message' => 'Document téléchargé et analysé avec succès',
                'document' => [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'formatted_file_size' => $document->formatted_file_size,
                    'category' => $document->category,
                    'ai_summary' => $document->fresh()->ai_summary,
                    'detected_tags' => $document->fresh()->detected_tags,
                    'is_processed' => $document->fresh()->is_processed
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Document analysis failed on upload', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document téléchargé. Analyse en cours...',
                'document' => [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'formatted_file_size' => $document->formatted_file_size,
                    'category' => $document->category,
                    'is_processed' => false
                ]
            ]);
        }
    }

    public function index()
    {
        $user = Auth::user();
        $documents = Document::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('documents.index', compact('documents'));
    }

    public function download($filename)
    {
        $user = Auth::user();
        $filePath = 'documents/' . $user->id . '/' . $filename;
        
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'Document non trouvé');
        }
        
        return Storage::disk('private')->download($filePath);
    }

    public function view($filename)
    {
        $user = Auth::user();
        $filePath = 'documents/' . $user->id . '/' . $filename;
        
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'Document non trouvé');
        }

        $document = Document::where('user_id', $user->id)
            ->where('filename', $filename)
            ->first();

        if (!$document) {
            abort(404, 'Document non trouvé');
        }

        // Servir le fichier avec le bon Content-Type pour affichage dans le navigateur
        $fileContents = Storage::disk('private')->get($filePath);
        
        return response($fileContents)
            ->header('Content-Type', $document->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"');
    }
    
    public function delete($filename)
    {
        $user = Auth::user();
        
        // Logger pour déboguer
        Log::info('Delete request received', [
            'user_id' => $user->id,
            'filename_param' => $filename,
            'filename_decoded' => urldecode($filename)
        ]);
        
        // Essayer d'abord avec le filename tel quel, puis décodé
        $document = Document::where('user_id', $user->id)
            ->where('filename', $filename)
            ->first();
            
        if (!$document) {
            $document = Document::where('user_id', $user->id)
                ->where('filename', urldecode($filename))
                ->first();
        }
            
        if (!$document) {
            Log::error('Document not found', [
                'user_id' => $user->id,
                'filename' => $filename,
                'filename_decoded' => urldecode($filename)
            ]);
            abort(404, 'Document non trouvé');
        }
        
        $filePath = 'documents/' . $user->id . '/' . $document->filename;
        
        // Supprimer le fichier physique s'il existe
        if (Storage::disk('private')->exists($filePath)) {
            Storage::disk('private')->delete($filePath);
        }
        
        // Supprimer l'enregistrement en base de données
        $document->delete();
        
        // Logger l'action
        Log::info('Document deleted', [
            'user_id' => $user->id,
            'document_id' => $document->id,
            'filename' => $document->filename
        ]);
        
        return redirect()->route('documents.index')->with('success', 'Document supprimé avec succès');
    }

    /**
     * Obtenir les détails d'un document avec analyse
     */
    public function show($id)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'formatted_file_size' => $document->formatted_file_size,
                'category' => $document->category,
                'ai_summary' => $document->ai_summary,
                'detected_tags' => $document->detected_tags,
                'ai_metadata' => $document->ai_metadata,
                'is_processed' => $document->is_processed,
                'processed_at' => $document->processed_at,
                'created_at' => $document->created_at
            ]
        ]);
    }

    /**
     * Relancer l'analyse d'un document
     */
    public function reanalyze($id)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        try {
            $analysisResult = $this->documentAnalysisService->analyzeDocument($document);
            
            return response()->json([
                'success' => true,
                'message' => 'Document analysé avec succès',
                'analysis' => $analysisResult
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des documents d'un utilisateur
     */
    public function stats()
    {
        $user = Auth::user();
        $stats = $this->documentAnalysisService->getUserDocumentStats($user->id);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}