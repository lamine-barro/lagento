@extends('layouts.guest')

@section('title', 'Configuration du profil - Étape 2')

@section('content')
<div class="min-h-screen bg-white flex flex-col p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="history.back()" class="btn btn-ghost p-2">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <div class="text-sm font-medium" style="color: var(--orange-primary);">Étape 2 sur 3</div>
        </div>
        <div class="w-10"></div> <!-- Spacer -->
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="h-2 rounded-full" style="background: var(--gray-100);">
            <div class="h-2 rounded-full transition-all duration-500" style="background: var(--orange-primary); width: 66.66%;"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 w-full" style="max-width: 720px; margin-left: auto; margin-right: auto;">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="user" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Contact
            </h1>
            <p style="color: var(--gray-700);">
                Coordonnées de contact et représentant du projet
            </p>
        </div>

        <form method="POST" action="{{ route('onboarding.step2') }}" class="space-y-6">
            @csrf

            <!-- Contact fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Téléphone</label>
                    <input type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="Ex: +225 07 00 00 00" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="input-field w-full" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Site web</label>
                    <input type="url" name="site_web" value="{{ old('site_web') }}" placeholder="https://" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nom & prénom du représentant</label>
                    <input type="text" name="nom_representant" value="{{ old('nom_representant') }}" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Position du représentant</label>
                    <input type="text" name="role_representant" value="{{ old('role_representant') }}" class="input-field w-full" />
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Réseaux sociaux</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="url" name="reseaux_instagram" placeholder="Instagram" class="input-field w-full" value="{{ old('reseaux_instagram') }}" />
                    <input type="url" name="reseaux_youtube" placeholder="YouTube" class="input-field w-full" value="{{ old('reseaux_youtube') }}" />
                    <input type="url" name="reseaux_x" placeholder="X (Twitter)" class="input-field w-full" value="{{ old('reseaux_x') }}" />
                    <input type="url" name="reseaux_tiktok" placeholder="TikTok" class="input-field w-full" value="{{ old('reseaux_tiktok') }}" />
                    <input type="url" name="reseaux_linkedin" placeholder="LinkedIn" class="input-field w-full" value="{{ old('reseaux_linkedin') }}" />
                    <input type="url" name="reseaux_facebook" placeholder="Facebook" class="input-field w-full" value="{{ old('reseaux_facebook') }}" />
                    <input type="url" name="reseaux_whatsapp" placeholder="WhatsApp" class="input-field w-full" value="{{ old('reseaux_whatsapp') }}" />
                </div>
            </div>
        </form>
    </div>

    <!-- Footer Navigation -->
    <div class="flex justify-between items-center mt-8">
        <button onclick="history.back()" class="btn btn-ghost">
            Retour
        </button>
        <button 
            type="submit" 
            class="btn btn-primary"
            onclick="document.querySelector('form').submit()"
        >
            Continuer
        </button>
    </div>
</div>
@endsection