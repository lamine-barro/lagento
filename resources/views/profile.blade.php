@extends('layouts.app')

@section('title', 'Mon Profil Entrepreneur')
@section('seo_title', 'Profil Entrepreneur - Gérez vos Informations Business | Agento')
@section('meta_description', 'Gérez votre profil entrepreneur sur Agento : informations personnelles, projets d\'entreprise, préférences et paramètres de votre assistant IA business personnalisé.')
@section('meta_keywords', 'profil entrepreneur, gestion compte business, paramètres assistant IA, profil startup, dashboard entrepreneur')
@section('meta_robots', 'noindex, nofollow')
@section('canonical_url', route('profile'))
@section('og_title', 'Mon Profil Entrepreneur - Agento')
@section('og_description', 'Gérez votre profil et paramètres sur Agento, votre assistant IA entrepreneurial personnalisé.')
@section('page_title', 'Mon Profil Entrepreneur')

@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "ProfilePage",
    "name": "Profil Entrepreneur Agento",
    "description": "Page de profil pour entrepreneurs utilisant l'assistant IA Agento",
    "mainEntity": {
        "@type": "Person",
        "name": "Entrepreneur"
    }
}
@endverbatim
@endsection

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
                        
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Mettre à jour
                            </button>
                        </div>
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

    <!-- Modal OTP pour changement d'email (dans le scope profileManager) -->
    <div x-show="showOtpModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" 
         style="background: rgba(0, 0, 0, 0.5); display: none;">
        
        <div @click.stop 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-4" style="color: var(--gray-900);">
                    Vérification de votre nouvelle adresse email
                </h3>
                
                <p class="text-sm text-gray-600 mb-6">
                    Un code de vérification a été envoyé à <strong x-text="newEmail"></strong>. 
                    Veuillez saisir le code à 6 chiffres reçu :
                </p>
                
                <input type="text" 
                       x-model="otpCode"
                       placeholder="000000"
                       maxlength="6"
                       class="input-field w-full text-center text-lg tracking-widest mb-6"
                       @input="otpCode = $event.target.value.replace(/[^0-9]/g, '').slice(0, 6)"
                       @keydown.enter="verifyOtp()">
                
                <div class="flex gap-3 mb-4">
                    <button @click="closeOtpModal()" 
                            class="btn btn-ghost flex-1">
                        Annuler
                    </button>
                    <button @click="verifyOtp()" 
                            :disabled="otpCode.length !== 6"
                            class="btn btn-primary flex-1">
                        Valider
                    </button>
                </div>
                
                <button @click="resendOtp()" 
                        class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                    Renvoyer le code
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div x-data="{ 
        open: false, 
        confirmText: '',
        deleteAccount() {
            if (this.confirmText === 'SUPPRIMER') {
                fetch('{{ route('profile.delete') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(() => {
                    window.location.href = '{{ route('landing') }}';
                });
            }
        }
    }" 
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
document.addEventListener('alpine:init', () => {
    Alpine.data('profileManager', () => ({
        profile: @json($user ?? []),
        project: @json($project ?? []),
        showOtpModal: false,
        newEmail: '',
        otpCode: '',
        
        init() {
            // Force l'initialisation des variables pour éviter les erreurs
            this.showOtpModal = false;
            this.newEmail = '';
            this.otpCode = '';
            console.log('ProfileManager initialized', {
                showOtpModal: this.showOtpModal,
                newEmail: this.newEmail,
                otpCode: this.otpCode
            });
        },
        
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
                    if (data.email_change_pending) {
                        // Afficher la modal OTP
                        this.newEmail = data.new_email;
                        this.showOtpModal = true;
                        this.otpCode = '';
                        
                        if (typeof window.showSuccessToast === 'function') {
                            window.showSuccessToast(data.message || 'Un code de vérification a été envoyé à votre nouvelle adresse email.');
                        }
                    } else {
                        if (typeof window.showSuccessToast === 'function') {
                            window.showSuccessToast(data.message || 'Profil mis à jour avec succès');
                        }
                    }
                } else {
                    if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast(data.message || 'Erreur lors de la mise à jour du profil');
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Erreur lors de la mise à jour du profil');
                }
            });
        },

        closeOtpModal() {
            this.showOtpModal = false;
            this.newEmail = '';
            this.otpCode = '';
            // Restaurer l'ancien email dans le formulaire
            this.profile.email = @json($user->email ?? '');
        },

        verifyOtp() {
            if (this.otpCode.length !== 6) {
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Veuillez saisir un code à 6 chiffres');
                }
                return;
            }

            fetch('{{ route("email-change.verify") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ otp: this.otpCode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showOtpModal = false;
                    this.profile.email = data.new_email;
                    
                    if (typeof window.showSuccessToast === 'function') {
                        window.showSuccessToast(data.message || 'Votre adresse email a été mise à jour avec succès');
                    }
                    
                    // Optionnel: actualiser la page pour refléter les changements
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast(data.message || 'Code OTP incorrect');
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Erreur lors de la vérification du code');
                }
            });
        },

        resendOtp() {
            fetch('{{ route("email-change.send-otp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: this.newEmail })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.otpCode = '';
                    if (typeof window.showSuccessToast === 'function') {
                        window.showSuccessToast(data.message || 'Un nouveau code a été envoyé');
                    }
                } else {
                    if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast(data.message || 'Erreur lors de l\'envoi du code');
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Erreur lors de l\'envoi du code');
                }
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
        }
    }))
});
</script>
@endpush
@endsection