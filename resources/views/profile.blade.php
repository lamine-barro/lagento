@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="min-h-screen bg-white" x-data="profileManager()">
    <!-- Profile Content -->
    <div class="max-w-4xl mx-auto p-4">
        <h2 class="font-medium mb-6" style="color: var(--gray-900);">Profil</h2>
        
        <div class="space-y-8">
            
            <!-- Informations personnelles -->
            <form @submit.prevent="updateProfile">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="font-medium mb-4" style="color: var(--gray-900);">Informations personnelles</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                                    Nom complet *
                                </label>
                                <input 
                                    type="text"
                                    x-model="profile.name"
                                    class="input-field w-full"
                                    required
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                                    Téléphone
                                </label>
                                <input 
                                    type="tel"
                                    x-model="profile.phone"
                                    placeholder="+225 XX XX XX XX XX"
                                    class="input-field w-full"
                                />
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                                    Email *
                                </label>
                                <input 
                                    type="email"
                                    x-model="profile.email"
                                    class="input-field w-full"
                                    required
                                />
                                <p class="text-xs mt-1" style="color: var(--gray-500);">
                                    Votre email de connexion. Un code de vérification sera envoyé si vous le modifiez.
                                </p>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            Mettre à jour
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Préférences -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="font-medium mb-4" style="color: var(--gray-900);">Préférences et confidentialité</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3">
                            <div>
                                <div class="text-sm font-medium" style="color: var(--gray-900);">Profil public</div>
                                <div class="text-xs" style="color: var(--gray-500);">Permettre aux autres utilisateurs de voir votre profil dans l'annuaire</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="profile.is_public" @change="updatePreferences()" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between p-3">
                            <div>
                                <div class="text-sm font-medium" style="color: var(--gray-900);">Notifications email</div>
                                <div class="text-xs" style="color: var(--gray-500);">Recevoir des notifications sur les opportunités et actualités</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="profile.email_notifications" @change="updatePreferences()" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Déconnexion -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="font-medium mb-2" style="color: var(--gray-900);">Déconnexion</h3>
                    <p class="text-sm mb-4" style="color: var(--gray-700);">
                        Se déconnecter de votre compte sur cet appareil.
                    </p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary text-sm">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Zone de danger -->
            <div class="card mb-60">
                <div class="card-body" style="background: var(--danger-50);">
                    <h3 class="font-medium mb-2" style="color: var(--danger);">Zone de danger</h3>
                    <p class="text-sm mb-4" style="color: var(--gray-700);">
                        Cette action est irréversible. Toutes vos données seront définitivement supprimées.
                    </p>
                    <button 
                        @click="$dispatch('open-delete-account-modal')"
                        class="btn-danger-outline text-sm"
                    >
                        Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div x-data="{ open: false }" 
     @open-delete-account-modal.window="open = true"
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center" 
     style="display: none;">
    
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 backdrop-blur-sm" style="background: rgba(0, 0, 0, 0.5);" @click="open = false"></div>
    
    <!-- Modal -->
    <div @click.stop 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-2xl">
        
        <div class="p-6">
            <!-- Header -->
            <h3 class="text-lg font-semibold mb-4" style="color: var(--gray-900);">
                Supprimer le compte
            </h3>
            
            <!-- Content -->
            <p class="text-sm mb-6" style="color: var(--gray-700);">
                Êtes-vous absolument sûr de vouloir supprimer votre compte ? Toutes vos données, conversations, projets et analytics seront définitivement supprimés.
            </p>
            
            <!-- Confirmation -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Tapez "SUPPRIMER" pour confirmer
                </label>
                <input 
                    type="text"
                    x-model="confirmText"
                    placeholder="SUPPRIMER"
                    class="input-field w-full"
                />
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3">
                <button 
                    type="button"
                    @click="open = false; confirmText = ''"
                    class="btn btn-ghost flex-1"
                >
                    Annuler
                </button>
                <button 
                    type="button"
                    @click="deleteAccount()"
                    :disabled="confirmText !== 'SUPPRIMER'"
                    class="btn-danger-outline flex-1 transition-all duration-200"
                    :style="confirmText !== 'SUPPRIMER' ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                >
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* Hide scrollbar but keep functionality */
.scrollbar-hidden {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hidden::-webkit-scrollbar {
    display: none;
}

/* Danger outline buttons */
.btn-danger-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.375rem;
    background-color: transparent;
    color: #dc2626;
    border: 1px solid #dc2626;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.btn-danger-outline:hover:not(:disabled) {
    background-color: #dc2626;
    color: white;
    border-color: #dc2626;
}

.btn-danger-outline:focus {
    outline: 2px solid #dc2626;
    outline-offset: 2px;
}

.btn-danger-outline:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Remove shadow from cards */
.card {
    box-shadow: none !important;
}
</style>

<script>
function profileManager() {
    return {
        profile: @json($user ?? []),
        project: @json($project ?? []),
        confirmText: '',
        
        updateProfile() {
            fetch('{{ route("profile.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.profile)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.email_changed) {
                        alert(data.message || 'Email mis à jour. Un code de vérification a été envoyé à votre nouvelle adresse.');
                        // Optionnel: rediriger vers la page de vérification OTP
                        // window.location.href = '{{ route("auth.verify-otp-form") }}';
                    } else {
                        alert(data.message || 'Profil mis à jour avec succès');
                    }
                } else {
                    alert('Erreur lors de la mise à jour du profil');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour du profil');
            });
        },
        
        
        updatePreferences() {
            fetch('{{ route("profile.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: this.profile.name,
                    email: this.profile.email,
                    phone: this.profile.phone,
                    is_public: this.profile.is_public,
                    email_notifications: this.profile.email_notifications
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show a subtle toast or visual feedback
                    console.log('Préférences mises à jour');
                }
            });
        },
        
        deleteAccount() {
            if (this.confirmText === 'SUPPRIMER') {
                fetch('{{ route("profile.delete") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(() => {
                    window.location.href = '{{ route("landing") }}';
                });
            }
        }
    }
}
</script>
@endpush
@endsection