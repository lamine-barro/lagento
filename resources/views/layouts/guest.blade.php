<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'LAgentO') - Assistant IA Entrepreneurial</title>
    <meta name="description" content="@yield('meta_description', 'Assistant IA dédié aux entrepreneurs ivoiriens')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-light.png" id="favicon">
    <link rel="apple-touch-icon" href="/favicon-light.png">
    <link rel="manifest" href="/site.webmanifest">
    
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
                    © 2025 LAgentO - <a href="{{ route('legal') }}" class="hover:underline" style="color: var(--gray-600);">Mentions légales</a>
                </p>
                <p class="text-xs" style="color: var(--gray-500);">
                    Développé avec ❤️ pour l'écosystème entrepreneurial ivoirien
                </p>
            </div>
        </footer>
    </div>
    
    @stack('scripts')
</body>
</html>