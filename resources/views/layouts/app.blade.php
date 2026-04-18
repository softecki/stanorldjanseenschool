<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'School') }} — @yield('title', 'App')</title>

    <link rel="icon" type="image/x-icon" href="{{ @globalAsset(setting('favicon')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full overflow-hidden bg-gray-100 font-sans text-gray-700 antialiased">
    <div
        x-data="{ sidebarOpen: window.innerWidth >= 1024, userMenuOpen: false }"
        class="flex h-full min-h-0 flex-col overflow-hidden"
        @keydown.window.escape="userMenuOpen = false"
    >
        {{-- Mobile sidebar overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        <div class="flex min-h-0 flex-1 overflow-hidden">
            {{-- Sidebar --}}
            <aside
                class="fixed inset-y-0 left-0 z-50 flex h-full min-h-0 w-64 flex-col border-r border-gray-200 bg-white shadow-sm transition-transform duration-200 ease-out lg:static lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-16 items-center gap-3 border-b border-gray-200 px-4">
                    <a href="{{ url('/') }}" class="flex min-w-0 flex-1 items-center gap-2 font-bold text-gray-900">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-brand-600 text-white shadow-sm">
                            @if(setting('dark_logo'))
                                <img src="{{ @globalAsset(setting('dark_logo'), '154X38.webp') }}" alt="" class="h-7 w-auto max-w-[2.25rem] object-contain" />
                            @else
                                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            @endif
                        </span>
                        <span class="truncate bg-gradient-to-r from-brand-700 to-brand-600 bg-clip-text text-lg font-bold text-transparent">
                            {{ config('app.name', 'School') }}
                        </span>
                    </a>
                </div>

                <nav class="flex-1 overflow-y-auto py-4">
                    @include('layouts.partials.app-sidebar-nav')
                </nav>
            </aside>

            {{-- Main column --}}
            <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                <header class="z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 shadow-sm">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="btn-icon lg:hidden"
                            @click="sidebarOpen = !sidebarOpen"
                            aria-label="{{ ___('common.Toggle navigation') }}"
                        >
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <h1 class="truncate text-lg font-semibold text-gray-800">@yield('page_title', config('app.name'))</h1>
                    </div>

                    <div class="flex items-center gap-2">
                        @auth
                            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                                <button
                                    type="button"
                                    class="flex max-w-[12rem] items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                                    @click="open = !open"
                                >
                                    <svg class="h-8 w-8 shrink-0 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="hidden truncate sm:inline">{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-transition
                                    @click.outside="open = false"
                                    x-cloak
                                    class="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black/5"
                                >
                                    @if (Route::has('my.profile'))
                                        <a href="{{ route('my.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                            {{ ___('common.my_profile') }}
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                            {{ ___('common.Logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="btn-secondary px-3 py-2 text-sm">{{ ___('common.Login') }}</a>
                                @endif
                                @if (Route::has('register.page'))
                                    <a href="{{ route('register.page') }}" class="btn-primary px-3 py-2 text-sm">{{ ___('common.Register') }}</a>
                                @endif
                            </div>
                        @endauth
                    </div>
                </header>

                <main class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                            {{ session('status') }}
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>

        <div id="loader" class="pointer-events-none fixed inset-0 z-[100] hidden items-center justify-center bg-white/80 backdrop-blur-sm">
            <div class="text-center">
                <svg class="mx-auto h-10 w-10 animate-spin text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <p class="mt-4 text-sm font-semibold text-gray-700">{{ __('Loading…') }}</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.sidebar-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    var el = document.getElementById('loader');
                    if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
