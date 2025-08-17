<?php

namespace App\Http\Controllers;

use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private UserAnalyticsService $analyticsService;

    public function __construct(UserAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:20480', // 20 Mo max
            'category' => 'nullable|string|in:business_plan,financial_docs,legal_docs,marketing,other'
        ]);

        $user = Auth::user();
        
        // Vérifier le nombre de documents existants (max 10)
        $path = 'documents/' . $user->id;
        $existingFiles = Storage::disk('private')->exists($path) ? 
            Storage::disk('private')->files($path) : [];
        
        if (count($existingFiles) >= 10) {
            return response()->json([
                'error' => 'Vous avez atteint la limite de 10 documents'
            ], 422);
        }
        $file = $request->file('document');
        
        // Store file
        $filePath = $file->store('documents/' . $user->id, 'private');
        
        // Get file info
        $fileInfo = [
            'filename' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'size_mb' => round($file->getSize() / 1024 / 1024, 2),
            'category' => $request->get('category', 'other'),
            'path' => $filePath
        ];

        // Track upload in analytics
        $this->analyticsService->trackDataSourceUpload($user, $fileInfo);

        return response()->json([
            'success' => true,
            'message' => 'Document téléchargé avec succès',
            'file_info' => $fileInfo
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        $path = 'documents/' . $user->id;
        
        // Vérifier si le dossier existe, sinon retourner un tableau vide
        if (Storage::disk('private')->exists($path)) {
            $documents = Storage::disk('private')->files($path);
        } else {
            $documents = [];
        }
        
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
    
    public function delete($filename)
    {
        $user = Auth::user();
        $filePath = 'documents/' . $user->id . '/' . $filename;
        
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'Document non trouvé');
        }
        
        Storage::disk('private')->delete($filePath);
        
        return redirect()->route('documents.index')->with('success', 'Document supprimé avec succès');
    }
}