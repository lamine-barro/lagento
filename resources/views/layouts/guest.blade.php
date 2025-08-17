<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>@yield('seo_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial Côte d\'Ivoire')</title>
    <meta name="description" content="@yield('meta_description', 'LAgentO : Premier assistant IA dédié aux entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, accompagnement 24/7 pour réussir en Côte d\'Ivoire.')">
    <meta name="keywords" content="@yield('meta_keywords', 'assistant IA côte ivoire, entrepreneuriat abidjan, startup ci, financement pme, conseil entrepreneur, innovation afrique')">
    <meta name="author" content="LAgentO - Lamine Barro">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial')">
    <meta property="og:description" content="@yield('og_description', 'LAgentO : Premier assistant IA pour entrepreneurs ivoiriens')">
    <meta property="og:image" content="@yield('og_image', asset('images/lagento-social-banner.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_CI">
    <meta property="og:site_name" content="LAgentO">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('twitter_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial')">
    <meta property="twitter:description" content="@yield('twitter_description', 'LAgentO : Premier assistant IA pour entrepreneurs ivoiriens')">
    <meta property="twitter:image" content="@yield('twitter_image', asset('images/lagento-social-banner.jpg'))">
    <meta property="twitter:creator" content="@LamBarro">
    <meta property="twitter:site" content="@LAgentO_CI">
    
    <!-- Géolocalisation -->
    <meta name="geo.region" content="CI">
    <meta name="geo.placename" content="Côte d'Ivoire">
    <meta name="geo.position" content="7.539989,-5.54708">
    
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    @yield('schema_org', json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'LAgentO',
        'description' => 'Assistant IA entrepreneurial pour la Côte d\'Ivoire',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/projets') . '?search={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ]
    ]))
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.png">
    <link rel="manifest" href="/site.webmanifest">
    
    
    
    <!-- Styles -->
    @if (View::hasSection('vite'))
        @yield('vite')
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    @stack('styles')
    @stack('head')
</head>
<body>
    <div class="min-h-screen">
        @yield('content')
    </div>
    
    @stack('scripts')
</body>
</html>