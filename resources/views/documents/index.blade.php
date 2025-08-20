@extends('layouts.app')

@section('title', 'Documents')

@push('styles')
<script>
// Définir la fonction documentUpload avant Alpine.js
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

@section('content')
<div class="container max-w-4xl mx-auto section px-4 sm:px-6 lg:px-8">
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
                        PDF, DOC, DOCX, XLS, XLSX, PNG, JPG • Images analysées par IA vision (max 20 Mo)
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
        <div class="grid gap-6 max-w-none">
            @foreach($documents as $document)
                @php
                    $docId = str_replace('-', '_', $document->id);
                @endphp
                <div class="document-card group relative bg-white rounded-xl border border-gray-200 hover:border-orange-200 transition-all duration-200 mt-4" 
                     x-data="{ showDetails: false, showPreview: false }">
                    
                    <div class="p-6 w-full">
                        <!-- Contenu principal -->
                        <div class="w-full">
                            <!-- En-tête du document -->
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate mb-2" title="{{ $document->original_name }}">
                                        {{ strlen($document->original_name) > 50 ? substr($document->original_name, 0, 47) . '...' : $document->original_name }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="hard-drive" class="w-4 h-4"></i>
                                            {{ $document->formatted_file_size }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="folder" class="w-4 h-4"></i>
                                            @php
                                                $categoryTranslations = [
                                                    'other' => 'Autre',
                                                    'official' => 'Officiel',
                                                    'business' => 'Business',
                                                    'legal' => 'Juridique',
                                                    'financial' => 'Financier'
                                                ];
                                            @endphp
                                            {{ $categoryTranslations[$document->category] ?? ucfirst($document->category) }}
                                        </span>
                                        @if($document->is_processed)
                                            <span class="text-gray-500">
                                                {{ $document->processed_at->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Actions rapides -->
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if(in_array($document->file_extension, ['pdf', 'png', 'jpg', 'jpeg']))
                                        <a href="{{ route('documents.view', $document->filename) }}" 
                                           target="_blank"
                                           class="p-2 rounded-lg hover:bg-blue-50 transition-colors"
                                           title="Ouvrir dans un nouvel onglet">
                                            <i data-lucide="external-link" class="w-4 h-4 text-blue-600"></i>
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('documents.download', $document->filename) }}" 
                                       download
                                       class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                       title="Télécharger">
                                        <i data-lucide="download" class="w-4 h-4 text-gray-600"></i>
                                    </a>
                                    
                                    <button @click="window.openDeleteModal({ id: '{{ $document->id }}', name: '{{ $document->original_name }}', filename: '{{ $document->filename }}' })" 
                                            class="p-2 rounded-lg hover:bg-red-50 transition-colors"
                                            title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4 text-red-600"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Tags détectés avec design amélioré -->
                            @if($document->is_processed && $document->detected_tags && is_array($document->detected_tags))
                                @php
                                    $filteredTags = array_filter($document->detected_tags, function($tag) {
                                        return strtolower($tag) !== 'autre';
                                    });
                                @endphp
                                @if(count($filteredTags) > 0)
                                    <div class="mb-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($filteredTags as $tag)
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border" 
                                                      style="background: var(--orange-50); color: var(--orange-700); border-color: var(--orange-200);">
                                                    <i data-lucide="tag" class="w-3 h-3 mr-1"></i>
                                                    @php
                                                        $displayTag = str_replace('_', ' ', $tag);
                                                        $displayTag = ucfirst($displayTag);
                                                    @endphp
                                                    {{ $displayTag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <!-- État du document avec messages informatifs -->
                            @if($document->is_processed && $document->ai_summary)
                                <div class="bg-gray-50 rounded-xl border border-gray-100" style="padding: var(--space-4);">
                                    <div class="flex items-start justify-between mb-3">
                                        <h4 class="text-sm font-semibold text-gray-900">Résumé synthétique</h4>
                                        <button @click="showDetails = !showDetails" 
                                                class="text-xs text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
                                            <span x-text="showDetails ? 'Réduire' : 'Voir plus'"></span>
                                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="showDetails && 'rotate-180'"></i>
                                        </button>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 leading-relaxed" :class="!showDetails && 'line-clamp-2'">
                                        {{ $document->ai_summary }}
                                    </p>
                                        
                                    @if($document->ai_metadata && isset($document->ai_metadata['document_type']))
                                        <div x-show="showDetails" x-transition class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="flex items-center justify-between text-sm">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="file-search" class="w-4 h-4 text-gray-500"></i>
                                                    <span class="text-gray-600">{{ $document->ai_metadata['document_type'] }}</span>
                                                </div>
                                                @if(isset($document->ai_metadata['confidence']))
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-gray-500">Confiance:</span>
                                                        <span class="font-medium text-gray-900">{{ round($document->ai_metadata['confidence'] * 100) }}%</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif(!$document->is_processed)
                                @if(in_array($document->file_extension, ['png', 'jpg', 'jpeg']))
                                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                            <div class="flex-1">
                                                <p class="text-sm text-blue-700 font-medium mb-1">
                                                    🤖 IA Vision GPT-4 analyse votre image...
                                                </p>
                                                <p class="text-xs text-blue-600">
                                                    Extraction intelligente du contenu, OCR et détection automatique
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 border-2 border-orange-600 border-t-transparent rounded-full animate-spin"></div>
                                            <div class="flex-1">
                                                <p class="text-sm text-orange-700 font-medium mb-1">
                                                    Intelligence artificielle en cours d'analyse...
                                                </p>
                                                <p class="text-xs text-orange-600">
                                                    Résumé et tags automatiques bientôt disponibles
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-300 transition-all">
            <div class="text-center py-16">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i data-lucide="file-plus" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun document analysé</h3>
                <p class="text-sm text-gray-600 mb-4 max-w-sm mx-auto">
                    Glissez vos documents officiels dans la zone de téléchargement ci-dessus pour commencer l'analyse automatique
                </p>
            </div>
        </div>
    @endif

</div>

<!-- Modal de suppression harmonisé -->
<div x-data="{ 
    showDeleteModal: false, 
    documentToDelete: null,
    deleting: false,
    
    init() {
        window.openDeleteModal = (doc) => {
            this.documentToDelete = doc;
            this.showDeleteModal = true;
        };
    },
    
    deleteDocument(doc) {
        if (!doc) return;
        
        this.deleting = true;
        
        // Créer un formulaire pour la suppression
        const form = window.document.createElement('form');
        form.method = 'POST';
        form.action = `/documents/delete/${encodeURIComponent(doc.filename)}`;
        
        // Ajouter le token CSRF
        const csrfToken = window.document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Ajouter la méthode DELETE
        const methodField = window.document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Ajouter au DOM et soumettre
        window.document.body.appendChild(form);
        form.submit();
    }
}">
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" 
         style="display: none;">
        
        <!-- Backdrop avec effet de flou -->
        <div class="fixed inset-0 backdrop-blur-sm" 
             style="background: rgba(0, 0, 0, 0.5);"
             @click="showDeleteModal = false; documentToDelete = null"></div>
        
        <!-- Modal -->
        <div @click.stop 
             x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-lg shadow-xl w-full max-w-md relative z-10">
            <div class="p-6 space-y-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <i data-lucide="trash-2" class="h-6 w-6 text-red-600"></i>
                    </div>
                    <div class="flex-1 space-y-2">
                        <h3 class="text-lg font-semibold" style="color: var(--gray-900);">Supprimer le document</h3>
                        <p class="text-sm text-gray-600">
                            Êtes-vous sûr de vouloir supprimer le document 
                            <span class="font-medium text-gray-900" x-text="documentToDelete?.name"></span> ?
                        </p>
                        <p class="text-xs text-gray-500">
                            Cette action est irréversible. Toutes les analyses IA associées seront également perdues.
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-3 justify-end">
                <button type="button" 
                        @click="showDeleteModal = false; documentToDelete = null"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors">
                    Annuler
                </button>
                <button type="button" 
                        @click="deleteDocument(documentToDelete)"
                        :disabled="deleting"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors disabled:opacity-50">
                    <span x-show="!deleting">Supprimer</span>
                    <span x-show="deleting" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Suppression...
                    </span>
                </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialiser les icônes Lucide après le chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.renderIcons === 'function') {
        window.renderIcons();
    }
});
</script>
@endpush
@endsection