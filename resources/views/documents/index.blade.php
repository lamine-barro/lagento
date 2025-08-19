@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="container max-w-4xl mx-auto section px-4">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('diagnostic') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors" title="Retour">
                <i data-lucide="arrow-left" class="w-5 h-5" style="color: var(--gray-600);"></i>
            </a>
            <h1 class="text-primary">Documents</h1>
        </div>
        <p class="text-secondary">Chargez vos documents officiels (DFE, registre de commerce, statuts...) pour des conseils personnalisés</p>
        <p class="text-xs mt-1" style="color: var(--gray-500);">Maximum 10 documents • 20 Mo par fichier</p>
    </div>

    <!-- Zone d'upload -->
    <div class="card mb-6">
        <div class="card-body">
            <div x-data="documentUpload()" class="space-y-4">
                <!-- Dropzone -->
                <div @drop.prevent="handleDrop($event)" 
                     @dragover.prevent 
                     @dragenter.prevent
                     @dragleave.prevent="isDragging = false"
                     @dragenter="isDragging = true"
                     class="border-2 border-dashed rounded-lg p-8 text-center transition-all cursor-pointer"
                     :class="isDragging ? 'border-orange-400 bg-orange-50' : 'border-gray-300 hover:border-gray-400'"
                     @click="$refs.fileInput.click()">
                    
                    <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto mb-4" 
                       :class="isDragging ? 'text-orange-500' : 'text-gray-400'"></i>
                    
                    <p class="text-sm font-medium mb-1" style="color: var(--gray-900);">
                        Glissez vos fichiers ici ou cliquez pour parcourir
                    </p>
                    <p class="text-xs" style="color: var(--gray-600);">
                        PDF, DOC, DOCX, XLS, XLSX, PNG, JPG (max 20 Mo)
                    </p>
                    
                    <input type="file" 
                           x-ref="fileInput" 
                           @change="handleFileSelect($event)" 
                           multiple 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                           class="hidden">
                </div>

                <!-- Files en cours d'upload -->
                <div x-show="uploadingFiles.length > 0" class="space-y-2">
                    <template x-for="file in uploadingFiles" :key="file.name">
                        <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--gray-50);">
                            <i data-lucide="file" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            <span class="flex-1 text-sm" x-text="file.name"></span>
                            <div class="w-24">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-500 transition-all" 
                                         :style="`width: ${file.progress}%`"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des documents -->
    @if(count($documents) > 0)
        <div class="space-y-4">
            @foreach($documents as $document)
                <div class="card p-4" x-data="{ showDetails: false }">
                    <div class="flex items-start gap-3">
                        <!-- Icône avec statut de traitement -->
                        <div class="relative">
                            @if($document->file_extension === 'pdf')
                                <i data-lucide="file-text" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                            @elseif(in_array($document->file_extension, ['doc', 'docx']))
                                <i data-lucide="file-type" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                            @elseif(in_array($document->file_extension, ['xls', 'xlsx']))
                                <i data-lucide="table" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                            @elseif(in_array($document->file_extension, ['png', 'jpg', 'jpeg']))
                                <i data-lucide="image" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                            @else
                                <i data-lucide="file" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                            @endif
                            
                            @if($document->is_processed)
                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white" title="Analysé"></div>
                            @else
                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full border-2 border-white animate-pulse" title="En cours d'analyse"></div>
                            @endif
                        </div>
                        
                        <!-- Informations principales -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-sm font-medium truncate" style="color: var(--gray-900);" title="{{ $document->original_name }}">
                                        {{ strlen($document->original_name) > 40 ? substr($document->original_name, 0, 37) . '...' : $document->original_name }}
                                    </h3>
                                    <p class="text-xs" style="color: var(--gray-500);">
                                        {{ $document->formatted_file_size }} • {{ ucfirst($document->category) }}
                                        @if($document->is_processed)
                                            • Analysé le {{ $document->processed_at->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-1 ml-2">
                                    @if($document->is_processed && $document->ai_summary)
                                        <button @click="showDetails = !showDetails" 
                                                class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                                title="Voir l'analyse">
                                            <i data-lucide="eye" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('documents.download', $document->filename) }}" 
                                       class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                       title="Télécharger">
                                        <i data-lucide="download" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('documents.delete', $document->filename) }}" 
                                          class="inline"
                                          onsubmit="return confirm('Supprimer ce document ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 rounded-lg hover:bg-red-50 transition-colors"
                                                title="Supprimer">
                                            <i data-lucide="x" class="w-4 h-4" style="color: var(--red-500);"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Tags détectés -->
                            @if($document->is_processed && $document->detected_tags && count($document->detected_tags) > 0)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($document->detected_tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                              style="background: var(--orange-100); color: var(--orange-700);">
                                            {{ str_replace('_', ' ', $tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Résumé IA (expandable) -->
                            @if($document->is_processed && $document->ai_summary)
                                <div x-show="showDetails" x-collapse class="mt-3 p-3 rounded-lg" style="background: var(--gray-50);">
                                    <h4 class="text-sm font-medium mb-2" style="color: var(--gray-900);">Résumé automatique :</h4>
                                    <p class="text-sm leading-relaxed" style="color: var(--gray-700);">{{ $document->ai_summary }}</p>
                                    
                                    @if($document->ai_metadata && isset($document->ai_metadata['document_type']))
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <p class="text-xs" style="color: var(--gray-500);">
                                                Type détecté: <span class="font-medium">{{ $document->ai_metadata['document_type'] }}</span>
                                                @if(isset($document->ai_metadata['confidence']))
                                                    • Confiance: {{ round($document->ai_metadata['confidence'] * 100) }}%
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @elseif(!$document->is_processed)
                                <div class="mt-3 p-3 rounded-lg" style="background: var(--yellow-50);">
                                    <p class="text-sm" style="color: var(--yellow-700);">
                                        <i data-lucide="clock" class="w-4 h-4 inline mr-1"></i>
                                        Analyse en cours... Le résumé sera disponible sous peu.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-12">
                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3" style="color: var(--gray-300);"></i>
                <p class="text-sm" style="color: var(--gray-600);">Aucun document</p>
                <p class="text-xs mt-1" style="color: var(--gray-500);">Glissez vos fichiers dans la zone au-dessus</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function documentUpload() {
    return {
        isDragging: false,
        uploadingFiles: [],
        
        handleDrop(event) {
            this.isDragging = false;
            const files = Array.from(event.dataTransfer.files);
            this.uploadFiles(files);
        },
        
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.uploadFiles(files);
        },
        
        uploadFiles(files) {
            // Vérifier le nombre de documents existants
            const existingDocs = {{ $documents->count() }};
            if (existingDocs + files.length > 10) {
                alert(`Vous ne pouvez pas dépasser 10 documents. Vous avez actuellement ${existingDocs} document(s).`);
                return;
            }
            
            files.forEach(file => {
                // Validation
                if (file.size > 20 * 1024 * 1024) {
                    alert(`${file.name} dépasse 20 Mo`);
                    return;
                }
                
                // Ajouter à la liste d'upload
                const uploadFile = {
                    name: file.name,
                    progress: 0
                };
                this.uploadingFiles.push(uploadFile);
                
                // Créer FormData
                const formData = new FormData();
                formData.append('document', file);
                
                // Upload avec progression
                const xhr = new XMLHttpRequest();
                
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        uploadFile.progress = Math.round((e.loaded / e.total) * 100);
                    }
                });
                
                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        // Succès - recharger la page
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        alert(`Erreur lors de l'upload de ${file.name}`);
                        this.uploadingFiles = this.uploadingFiles.filter(f => f !== uploadFile);
                    }
                });
                
                xhr.addEventListener('error', () => {
                    alert(`Erreur lors de l'upload de ${file.name}`);
                    this.uploadingFiles = this.uploadingFiles.filter(f => f !== uploadFile);
                });
                
                xhr.open('POST', '{{ route("documents.upload") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.send(formData);
            });
        }
    }
}
</script>
@endpush
@endsection