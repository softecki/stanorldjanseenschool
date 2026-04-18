<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'School') }} — @yield('title', ___('common.Login'))</title>
    <link rel="icon" type="image/x-icon" href="{{ @globalAsset(setting('favicon')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full bg-gradient-to-br from-slate-100 via-white to-blue-50 font-sans text-gray-800 antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
