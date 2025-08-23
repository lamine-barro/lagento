@extends('layouts.guest')

@section('seo_title', 'Agento - Assistant IA entrepreneurial n°1 en Côte d\'Ivoire')
@section('meta_description', 'Découvrez Agento, le premier assistant IA spécialement conçu pour accompagner les entrepreneurs ivoiriens.')
@section('meta_keywords', 'assistant IA côte ivoire, entrepreneur abidjan, startup CI, financement pme')
@section('title', 'Agento - Assistant IA entrepreneurial')

@section('vite')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@section('content')
<style>
    .two-column-layout {
        display: flex;
        min-height: 100vh;
        transition: background-color 0.3s ease;
    }
    
    /* Light mode */
    .left-column {
        width: 50%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem;
        background: white;
        transition: background-color 0.3s ease;
    }
    
    .right-column {
        width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        background: white;
        transition: background 0.3s ease;
    }
    
    /* Dark mode */
    [data-theme="dark"] .two-column-layout {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .left-column {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .right-column {
        background: #0a0a0a;
    }
    
    [data-theme="dark"] .main-title {
        color: #f5f5f5 !important;
    }
    
    [data-theme="dark"] .subtitle {
        color: #a0a0a0 !important;
    }
    
    [data-theme="dark"] .description {
        color: #cccccc !important;
    }
    
    [data-theme="dark"] .email-input {
        background: #2a2a2a !important;
        border-color: #404040 !important;
        color: #f5f5f5 !important;
    }
    
    [data-theme="dark"] .email-input::placeholder {
        color: #808080 !important;
    }
    
    .email-input:focus {
        border-color: var(--orange) !important;
        outline: none;
    }
    
    .submit-button {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        background: var(--orange-dark) !important;
    }
    
    .illustration-container {
        position: relative;
        width: 100%;
        max-width: 600px;
    }
    
    .illustration-image {
        width: 100%;
        height: auto;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .illustration-image:hover {
        transform: scale(1.02);
    }
    
    
    /* Decorative elements */
    .decoration-1 {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255, 107, 53, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    .decoration-2 {
        position: absolute;
        bottom: -20px;
        left: -20px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(255, 153, 102, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    /* Gestion responsive des illustrations */
    .mobile-illustration {
        display: none;
    }
    
    /* Prévenir le débordement horizontal sur tous les appareils */
    body {
        overflow-x: hidden !important;
    }
    
    .two-column-layout {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    
    @media (max-width: 1024px) {
        .two-column-layout {
            flex-direction: column;
        }
        .left-column, .right-column {
            width: 100%;
            padding: 2rem;
            overflow-x: hidden !important;
        }
        .main-title {
            font-size: 2.5rem !important;
        }
        
        /* Afficher l'illustration mobile et masquer celle de droite */
        .mobile-illustration {
            display: block;
        }
        .right-column {
            display: none;
        }
        
        /* Optimisation des espacements mobile */
        .logo-container {
            margin-bottom: 1.5rem !important;
        }
        
        .mobile-illustration {
            margin-bottom: 2rem !important;
        }
        
        .main-title {
            font-size: 2.25rem !important;
            margin-bottom: 1.5rem !important;
            line-height: 1.3 !important;
        }
        
        /* Ajuster le span dans le titre pour mobile */
        .main-title span {
            display: block !important;
            padding: 0.5rem 1rem !important;
            margin: 0.5rem 0 !important;
        }
        
        .description {
            font-size: 1rem !important;
            line-height: 1.6 !important;
            margin-bottom: 2rem !important;
        }
        
        .description span {
            font-size: 1.125rem !important;
        }
        
        /* Masquer les éléments décoratifs sur mobile */
        .decoration-1, .decoration-2 {
            display: none !important;
        }
    }
    
    /* Espacements encore plus compacts sur très petits écrans */
    @media (max-width: 640px) {
        .left-column {
            padding: 1.5rem !important;
            width: 100% !important;
            max-width: 100vw !important;
            box-sizing: border-box !important;
        }
        
        .logo-container {
            margin-bottom: 1rem !important;
        }
        
        .mobile-illustration {
            margin-bottom: 1.5rem !important;
        }
        
        .mobile-illustration div {
            max-width: 320px !important;
        }
        
        .main-title {
            font-size: 2rem !important;
            margin-bottom: 1rem !important;
        }
        
        /* Réduire encore plus le padding du span sur petits écrans */
        .main-title span {
            padding: 0.375rem 0.75rem !important;
            font-size: 1.75rem !important;
        }
        
        .description {
            margin-bottom: 1.5rem !important;
        }
        
        /* Adapter le formulaire pour mobile */
        form {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Stack le formulaire en colonne sur mobile */
        form > div {
            flex-direction: column !important;
        }
        
        .email-input, .submit-button {
            width: 100% !important;
        }
    }
</style>

<div class="two-column-layout" data-theme="light">
    <!-- COLONNE GAUCHE - Texte et Formulaire -->
    <div class="left-column">
        <!-- Logo -->
        <div class="logo-container" style="margin-bottom: 3rem;">
            <x-logo size="xl" />
        </div>
        
        <!-- Illustration Mobile uniquement -->
        <div class="mobile-illustration" style="margin-bottom: 3rem;">
            <div style="width: 100%; max-width: 400px; margin: 0 auto;">
                <img 
                    src="{{ asset('lagento_illustration.png') }}" 
                    alt="LagentO - Assistant IA pour entrepreneurs" 
                    style="width: 100%; height: auto; border-radius: 1.5rem; box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.2);"
                    loading="eager"
                />
            </div>
        </div>
        
        <!-- Titre Principal -->
        <h1 class="main-title" style="font-size: 3.5rem; font-weight: bold; line-height: 1.2; margin-bottom: 2rem; color: var(--gray-900);">
            Le Président Inspire,<br>
            <span style="display: inline-block; background: var(--orange); color: white; padding: 0.5rem 2rem; border-radius: var(--radius-lg); margin: 0.5rem 0; box-shadow: var(--shadow-lg);">
                LagentO Soutient,
            </span><br>
            La Jeunesse Construit
        </h1>
        
        <!-- Description -->
        <p class="description" style="font-size: 1.25rem; line-height: 1.8; color: var(--gray-700); margin-bottom: 3rem;">
            LagentO a cartographié <span style="color: var(--orange); font-weight: bold; font-size: 1.5rem;">1,000+ milliards de FCFA</span> 
            d'opportunités pour les jeunes entrepreneurs en Côte d'Ivoire à travers les initiatives 
            gouvernementales et les écosystèmes. Il est disponible 24h/7 pour vous accompagner à les saisir.
        </p>
        
        <!-- Formulaire Email -->
        <form method="POST" action="{{ route('auth.email') }}" style="max-width: 500px;">
            @csrf
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Votre adresse email"
                    value="{{ old('email') }}"
                    class="email-input"
                    style="flex: 1; padding: 1rem 1.5rem; font-size: 1.125rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-900); transition: var(--transition);"
                    required
                    autofocus
                />
                <button 
                    type="submit" 
                    class="submit-button"
                    style="padding: 1rem 2rem; background: var(--orange); color: white; font-weight: var(--font-weight-semibold); font-size: 1.125rem; border: none; border-radius: var(--radius-lg); cursor: pointer; white-space: nowrap; transition: var(--transition);"
                >
                    Évaluer mon projet
                </button>
            </div>
            @error('email')
                <p style="color: var(--orange); font-size: 0.875rem;">{{ $message }}</p>
            @enderror
        </form>
    </div>
    
    <!-- COLONNE DROITE - Illustration -->
    <div class="right-column">
        <div class="illustration-container">
            <!-- Decorative elements -->
            <div class="decoration-1"></div>
            <div class="decoration-2"></div>
            
            <!-- Main illustration -->
            <img 
                src="{{ asset('lagento_illustration.png') }}" 
                alt="LagentO - Assistant IA pour entrepreneurs" 
                class="illustration-image"
                loading="eager"
            />
        </div>
    </div>
</div>

<script>
    // Theme detection and switching
    document.addEventListener('DOMContentLoaded', function() {
        const layout = document.querySelector('.two-column-layout');
        
        // Check for saved theme preference or default to light
        const currentTheme = localStorage.getItem('theme') || 'light';
        layout.setAttribute('data-theme', currentTheme);
        
        // Listen for theme changes from other parts of the app
        window.addEventListener('theme-changed', function(e) {
            layout.setAttribute('data-theme', e.detail.theme);
        });
        
        // Check system preference
        if (!localStorage.getItem('theme')) {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                layout.setAttribute('data-theme', 'dark');
            }
        }
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('theme')) {
                layout.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    });
</script>
@endsection