@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 2')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <x-onboarding.header :current-step="2" />

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">

        <form id="step2-form" method="POST" action="{{ route('onboarding.step2.process') }}" class="space-y-6 mt-4">
            @csrf

            <!-- Contact principal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Téléphone -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="phone" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Téléphone *
                    </label>
                    <input type="tel" name="telephone" value="{{ old('telephone', $projet->telephone ?? '') }}" placeholder="Ex: +225 07 00 00 00" class="input-field w-full" maxlength="20" required />
                </div>
                
                <!-- Email -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="mail" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Email *
                    </label>
                    <input type="email" name="email" value="{{ old('email', $projet->email ?? auth()->user()->email) }}" class="input-field w-full" maxlength="190" required />
                </div>
                
                <!-- Nom & prénom du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="user" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Nom & prénom du représentant *
                    </label>
                    <input type="text" name="nom_representant" value="{{ old('nom_representant', $projet->nom_representant ?? '') }}" placeholder="Ex: Jean Kouassi" class="input-field w-full" maxlength="120" required />
                </div>
                
                <!-- Position du représentant -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="briefcase" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Position du représentant *
                    </label>
                    <input type="text" name="role_representant" value="{{ old('role_representant', $projet->role_representant ?? '') }}" placeholder="Ex: PDG, Directeur" class="input-field w-full" maxlength="80" required />
                </div>
                
                <!-- Site web -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="globe" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        Site web
                    </label>
                    <input type="url" name="site_web" value="{{ old('site_web', $projet->site_web ?? '') }}" placeholder="https://monsite.com" class="input-field w-full" maxlength="200" />
                </div>
                
                <!-- WhatsApp -->
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                        <i data-lucide="message-circle" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                        WhatsApp Business
                    </label>
                    <input type="url" name="reseaux_whatsapp" value="{{ old('reseaux_whatsapp', $projet->reseaux_sociaux['whatsapp_business'] ?? '') }}" placeholder="https://wa.me/22507000000" class="input-field w-full" maxlength="200" />
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <!-- LinkedIn -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="linkedin" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            LinkedIn
                        </label>
                        <input type="url" name="reseaux_linkedin" value="{{ old('reseaux_linkedin', $projet->reseaux_sociaux['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/votre-profil" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- Facebook -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="facebook" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Facebook
                        </label>
                        <input type="url" name="reseaux_facebook" value="{{ old('reseaux_facebook', $projet->reseaux_sociaux['facebook'] ?? '') }}" placeholder="https://facebook.com/votre-page" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- YouTube -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="youtube" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            YouTube
                        </label>
                        <input type="url" name="reseaux_youtube" value="{{ old('reseaux_youtube', $projet->reseaux_sociaux['youtube'] ?? '') }}" placeholder="https://youtube.com/@votre-chaine" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- Instagram -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="instagram" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            Instagram
                        </label>
                        <input type="url" name="reseaux_instagram" value="{{ old('reseaux_instagram', $projet->reseaux_sociaux['instagram'] ?? '') }}" placeholder="https://instagram.com/votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- X (Twitter) -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="twitter" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            X (Twitter)
                        </label>
                        <input type="url" name="reseaux_x" value="{{ old('reseaux_x', $projet->reseaux_sociaux['x'] ?? '') }}" placeholder="https://x.com/votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                    
                    <!-- TikTok -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--gray-700);">
                            <i data-lucide="video" class="w-4 h-4" style="stroke-width: 1.25;"></i>
                            TikTok
                        </label>
                        <input type="url" name="reseaux_tiktok" value="{{ old('reseaux_tiktok', $projet->reseaux_sociaux['tiktok'] ?? '') }}" placeholder="https://tiktok.com/@votre-compte" class="input-field w-full" maxlength="200" />
                    </div>
                </div>
        </form>
    </div>

    <x-onboarding.footer :next-form-id="'step2-form'" next-label="Suivant" />
</div>
@endsection