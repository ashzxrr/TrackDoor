<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'TrackDoor')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        @yield('content')
        @stack('scripts')

        <div id="loading-screen" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/90 p-6 backdrop-blur-sm" role="status" aria-live="polite" aria-label="Memuat" aria-hidden="true">
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-700 border-t-yellow-400" aria-hidden="true"></div>
                <p id="loading-message" class="text-sm font-semibold text-white">Memproses...</p>
            </div>
        </div>

        <script>
            (() => {
                const loadingScreen = document.getElementById('loading-screen');
                const loadingMessage = document.getElementById('loading-message');

                window.showLoadingScreen = (message = 'Memproses...') => {
                    loadingMessage.textContent = message;
                    loadingScreen.classList.remove('hidden');
                    loadingScreen.classList.add('flex');
                    loadingScreen.setAttribute('aria-hidden', 'false');
                    document.body.setAttribute('aria-busy', 'true');
                };

                window.hideLoadingScreen = () => {
                    loadingScreen.classList.add('hidden');
                    loadingScreen.classList.remove('flex');
                    loadingScreen.setAttribute('aria-hidden', 'true');
                    document.body.removeAttribute('aria-busy');
                };

                document.addEventListener('submit', (event) => {
                    if (event.target.matches('form:not([data-loading="manual"])')) {
                        window.showLoadingScreen('Menyimpan data...');
                    }
                });
            })();
        </script>
    </body>
</html>
