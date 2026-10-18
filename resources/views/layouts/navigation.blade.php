<aside class="border-b border-slate-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 lg:border-b-0 lg:border-r">
    <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4 lg:h-20 lg:px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center bg-cyan-700 font-mono text-sm font-semibold text-white">SM</span>
            <span><span class="block font-mono text-xs font-semibold uppercase tracking-[.12em] text-slate-900">StockManager</span><span class="block text-xs text-slate-500">Gestion d’entrepôt</span></span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">@csrf <button class="text-sm font-medium text-slate-600">Sortir</button></form>
    </div>
    <nav class="flex overflow-x-auto px-2 py-2 lg:block lg:px-0 lg:py-5">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">01</span> Tableau de bord</a>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index', 'products.create', 'products.edit', 'products.show') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">02</span> Produits</a>
        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">03</span> Clients</a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">04</span> Catégories</a>
        <a href="{{ route('products.archived') }}" class="nav-link {{ request()->routeIs('products.archived') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">05</span> Archives</a>
        <a href="{{ route('exports.inventory') }}" class="nav-link {{ request()->routeIs('exports.*') ? 'nav-link-active' : '' }}"><span class="font-mono text-xs">06</span> Exporter Excel</a>
    </nav>
    <div class="hidden lg:absolute lg:bottom-0 lg:left-0 lg:right-0 lg:block lg:border-t lg:border-slate-200 lg:p-5">
        <div class="mb-3 text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</div>
        <div class="mb-4 truncate text-xs text-slate-500">{{ Auth::user()->email }}</div>
        <div class="flex gap-3 text-xs font-medium"><a href="{{ route('profile.edit') }}" class="text-slate-600 hover:text-cyan-700">Profil</a><form method="POST" action="{{ route('logout') }}">@csrf <button class="text-slate-600 hover:text-cyan-700">Déconnexion</button></form></div>
    </div>
</aside>
