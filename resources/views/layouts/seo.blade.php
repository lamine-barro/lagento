<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>@yield('seo_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial Côte d\'Ivoire')</title>
    <meta name="description" content="@yield('meta_description', 'LAgentO : Premier assistant IA dédié aux entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, accompagnement 24/7 pour réussir en Côte d\'Ivoire.')">
    <meta name="keywords" content="@yield('meta_keywords', 'entrepreneuriat côte ivoire, assistant IA, startup abidjan, financement entreprise, accompagnement entrepreneur, business plan, opportunités ci, innovation afrique')">
    <meta name="author" content="@yield('meta_author', 'LAgentO - Lamine Barro')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', '@yield('seo_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial Côte d\'Ivoire')')">
    <meta property="og:description" content="@yield('og_description', '@yield('meta_description', 'LAgentO : Premier assistant IA dédié aux entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, accompagnement 24/7 pour réussir en Côte d\'Ivoire.')')">
    <meta property="og:image" content="@yield('og_image', asset('images/lagento-social-banner.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_CI">
    <meta property="og:site_name" content="LAgentO">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="@yield('twitter_url', url()->current())">
    <meta property="twitter:title" content="@yield('twitter_title', '@yield('seo_title', config('app.name', 'LAgentO') . ' - Assistant IA Entrepreneurial Côte d\'Ivoire')')">
    <meta property="twitter:description" content="@yield('twitter_description', '@yield('meta_description', 'LAgentO : Premier assistant IA dédié aux entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, accompagnement 24/7 pour réussir en Côte d\'Ivoire.')')">
    <meta property="twitter:image" content="@yield('twitter_image', asset('images/lagento-social-banner.jpg'))">
    <meta property="twitter:creator" content="@LamBarro">
    <meta property="twitter:site" content="@LAgentO_CI">
    
    <!-- Géolocalisation -->
    <meta name="geo.region" content="CI">
    <meta name="geo.placename" content="Côte d'Ivoire">
    <meta name="geo.position" content="7.539989,-5.54708">
    <meta name="ICBM" content="7.539989,-5.54708">
    
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    @yield('schema_org', json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'LAgentO',
        'description' => 'Assistant IA entrepreneurial pour la Côte d\'Ivoire',
        'url' => url('/'),
        'logo' => asset('images/lagento-logo.png'),
        'sameAs' => [
            'https://linkedin.com/company/lagento',
            'https://twitter.com/LAgentO_CI'
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'CI',
            'addressRegion' => 'Abidjan'
        ],
        'founder' => [
            '@type' => 'Person',
            'name' => 'Lamine Barro',
            'sameAs' => 'https://linkedin.com/in/laminebarro'
        ]
    ]))
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    @stack('head')
</head>
<body @yield('body_attributes', '')>
    @yield('content')
    
    @stack('scripts')
    @stack('footer')
</body>
</html>