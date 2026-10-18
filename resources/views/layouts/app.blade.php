<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'StockManager') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="app-shell">
            @include('layouts.navigation')
            <main class="lg:pl-64">
                <div class="content-wrap">
                    @if (session('success'))
                        <div class="mb-6 border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
