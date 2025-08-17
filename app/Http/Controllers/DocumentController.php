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
            'file' => 'required|file|max:10240', // 10MB max
            'category' => 'nullable|string|in:business_plan,financial_docs,legal_docs,marketing,other'
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        
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
        $documents = Storage::disk('private')->files('documents/' . $user->id);
        
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
}