<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>@yield('seo_title', 'Agento - Assistant IA Entrepreneurial Côte d\'Ivoire')</title>
    <meta name="description" content="@yield('meta_description', 'Agento, le premier assistant IA entrepreneurial de Côte d\'Ivoire. Conseils personnalisés, opportunités de financement, diagnostic d\'entreprise et accompagnement business 24/7 pour entrepreneurs ivoiriens.')">
    <meta name="keywords" content="@yield('meta_keywords', 'assistant IA côte ivoire, entrepreneur ivoirien, startup abidjan, financement PME, conseil business, diagnostic entreprise, innovation afrique, lamine barro, agento')">
    <meta name="author" content="Agento - L'équipe Agento">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:site_name" content="Agento">
    <meta property="og:title" content="@yield('og_title', 'Agento - Assistant IA Entrepreneurial')">
    <meta property="og:description" content="@yield('og_description', 'Agento, le premier assistant IA entrepreneurial de Côte d\'Ivoire. Conseils personnalisés, opportunités de financement et accompagnement business 24/7.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/agento-social-preview.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_CI">
    <meta property="og:locale:alternate" content="fr_FR">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@Agento_CI">
    <meta name="twitter:creator" content="@LamBarro">
    <meta name="twitter:title" content="@yield('twitter_title', 'Agento - Assistant IA Entrepreneurial')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Assistant IA entrepreneurial pour la Côte d\'Ivoire')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/agento-social-preview.jpg'))">
    
    <!-- Additional Meta Tags -->
    <meta name="theme-color" content="#ff6b35">
    <meta name="msapplication-TileColor" content="#ff6b35">
    <meta name="application-name" content="Agento">
    <meta name="apple-mobile-web-app-title" content="Agento">
    <meta name="format-detection" content="telephone=no">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-light.png" id="favicon">
    <link rel="apple-touch-icon" href="/favicon-light.png">
    <link rel="manifest" href="/site.webmanifest">
    
    @hasSection('schema_org')
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
        @yield('schema_org')
    </script>
    @endif
    
    <!-- Theme Script -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 
                         (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
            
            const favicon = document.getElementById('favicon');
            if (favicon) {
                favicon.href = theme === 'dark' ? '/favicon-dark.png' : '/favicon-light.png';
            }
        })();
    </script>
    
    @include('includes.google-analytics')
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="background: var(--gray-50);">
    <div class="page min-h-screen flex flex-col">
        <main class="flex-1">
            @yield('content')
        </main>
        <footer style="padding: 12px; border-top: 1px solid var(--gray-200); background: var(--surface-elevated, var(--white));">
            <div class="text-center">
                <p class="text-sm mb-1" style="color: var(--gray-600);">
                    © 2025 Agento - <a href="{{ route('legal') }}" class="hover:underline" style="color: var(--gray-600);">Mentions légales</a>
                </p>
                <p class="text-xs" style="color: var(--gray-500);">
                    Développé par <a href="https://horizon-o.ci" class="hover:underline" style="color: var(--gray-600);">Horizon O</a> avec ❤️ pour l'écosystème entrepreneurial ivoirien
                </p>
            </div>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>