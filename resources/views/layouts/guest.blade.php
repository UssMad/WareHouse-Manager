<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'StockManager') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-900">
        <div class="min-h-screen bg-[linear-gradient(120deg,rgba(8,47,73,.93),rgba(15,23,42,.98))] px-4 py-10 sm:px-6">
            <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-5xl overflow-hidden border border-slate-700 bg-slate-100 shadow-2xl lg:grid-cols-[1.05fr_.95fr]">
                <div class="hidden bg-cyan-800 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div>
                        <div class="font-mono text-xs uppercase tracking-[.22em] text-cyan-200">Inventaire / contrôle</div>
                        <h1 class="mt-6 text-5xl font-semibold leading-tight tracking-tight">Le stock, sans angle mort.</h1>
                        <p class="mt-6 max-w-sm text-base leading-7 text-cyan-100">Centralisez chaque produit et chaque mouvement depuis un espace conçu pour le terrain.</p>
                    </div>
                    <div class="border-t border-cyan-600 pt-5 font-mono text-xs text-cyan-200">STOCKMANAGER — OPÉRATIONS</div>
                </div>
                <div class="flex items-center justify-center p-6 sm:p-10">
                    <div class="w-full max-w-sm">
                        <a href="{{ route('login') }}" class="mb-9 flex items-center gap-3 text-slate-900">
                            <span class="grid h-10 w-10 place-items-center bg-cyan-700 font-mono text-lg font-semibold text-white">SM</span>
                            <span><span class="block font-mono text-xs uppercase tracking-[.14em] text-cyan-700">StockManager</span><span class="text-sm font-medium text-slate-500">Gestion d’entrepôt</span></span>
                        </a>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
