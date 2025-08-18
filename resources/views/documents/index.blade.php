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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($documents as $document)
                @php
                    $filename = basename($document);
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $size = Storage::disk('private')->size($document);
                    $sizeFormatted = $size > 1048576 ? 
                        round($size / 1048576, 1) . ' Mo' : 
                        round($size / 1024) . ' Ko';
                    
                    // Simplifier le nom du fichier si trop long
                    $displayName = strlen($filename) > 30 ? 
                        substr($filename, 0, 27) . '...' : 
                        $filename;
                @endphp
                
                <div class="card p-4 flex items-center gap-3">
                    <!-- Icône minimaliste -->
                    @if(in_array($extension, ['pdf']))
                        <i data-lucide="file-text" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                    @elseif(in_array($extension, ['doc', 'docx']))
                        <i data-lucide="file-type" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                    @elseif(in_array($extension, ['xls', 'xlsx']))
                        <i data-lucide="table" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                    @elseif(in_array($extension, ['png', 'jpg', 'jpeg']))
                        <i data-lucide="image" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                    @else
                        <i data-lucide="file" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                    @endif
                    
                    <!-- Nom et taille -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color: var(--gray-900);" title="{{ $filename }}">
                            {{ $displayName }}
                        </p>
                        <p class="text-xs" style="color: var(--gray-500);">{{ $sizeFormatted }}</p>
                    </div>
                    
                    <!-- Actions simplifiées -->
                    <a href="{{ route('documents.download', $filename) }}" 
                       class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                       title="Télécharger">
                        <i data-lucide="download" class="w-4 h-4" style="color: var(--gray-500);"></i>
                    </a>
                    
                    <form method="POST" action="{{ route('documents.delete', $filename) }}" 
                          class="inline"
                          onsubmit="return confirm('Supprimer ce document ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="p-1.5 rounded-lg hover:bg-red-50 transition-colors"
                                title="Supprimer">
                            <i data-lucide="x" class="w-4 h-4" style="color: var(--gray-500);"></i>
                        </button>
                    </form>
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
            const existingDocs = {{ count($documents) }};
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