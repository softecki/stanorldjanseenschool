<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name', 'School') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ @globalAsset(setting('favicon')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-blue-50 font-sans text-gray-800 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            <div class="card overflow-hidden shadow-lg ring-1 ring-black/5">
                <div class="card-body relative">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-brand-600/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-8 -left-8 h-28 w-28 rounded-full bg-amber-400/10 blur-2xl"></div>

                    <div class="relative z-10 mb-8 text-center">
                        <a href="{{ url('/') }}" class="inline-block transition hover:opacity-90">
                            <img class="setting-image mx-auto max-h-[60px]" src="{{ @globalAsset(setting('dark_logo'), '154X38.webp') }}" alt="{{ config('app.name') }}">
                        </a>
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <script src="{{ global_asset('backend') }}/assets/js/jquery-3.6.0.min.js"></script>
    <script src="{{ global_asset('backend') }}/assets/js/plugin.js"></script>
    <script src="{{ global_asset('backend') }}/assets/js/custom.js"></script>

    @include('backend.partials.alert-message')
    @yield('script')
</body>
</html>
