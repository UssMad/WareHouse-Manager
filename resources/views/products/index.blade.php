<x-app-layout>
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div><p class="page-kicker">Référentiel / stock actif</p><h1 class="page-title mt-2">Produits</h1></div><div class="flex flex-wrap gap-3"><a href="{{ route('exports.inventory') }}" class="btn-secondary">↓ Exporter Excel</a><a href="{{ route('products.create') }}" class="btn-primary">+ Nouveau produit</a></div></div>
    <section class="panel">
        <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[1fr_190px_190px_auto]">
            <input class="form-input" name="search" value="{{ request('search') }}" placeholder="Rechercher un nom ou une référence">
            <select class="form-input" name="category"><option value="">Toutes les catégories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select class="form-input" name="status"><option value="">Tous les statuts</option><option value="available" @selected(request('status') === 'available')>Disponible</option><option value="low" @selected(request('status') === 'low')>À surveiller / rupture</option></select>
            <button class="btn-secondary">Filtrer</button>
        </form>
        <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="table-head"><tr><th class="px-5 py-3">Produit</th><th class="px-5 py-3">Catégorie</th><th class="px-5 py-3 text-right">Prix unitaire</th><th class="px-5 py-3 text-right">Stock</th><th class="px-5 py-3">Statut</th><th class="px-5 py-3"></th></tr></thead><tbody>
            @forelse ($products as $product)
                <tr class="table-row"><td class="px-5 py-4"><a href="{{ route('products.show', $product) }}" class="font-semibold hover:text-cyan-700">{{ $product->name }}</a><p class="mt-1 font-mono text-xs text-slate-500">{{ $product->reference }}</p></td><td class="px-5 py-4 text-slate-600">{{ $product->category->name }}</td><td class="px-5 py-4 text-right font-mono">{{ number_format($product->unit_price, 2, ',', ' ') }} DH</td><td class="px-5 py-4 text-right font-mono font-semibold">{{ $product->stock_quantity }} <span class="text-xs font-normal text-slate-500">/ seuil {{ $product->alert_threshold }}</span></td><td class="px-5 py-4">@if($product->stock_quantity === 0)<span class="status-out">Rupture</span>@elseif($product->stock_quantity <= $product->alert_threshold)<span class="status-low">À surveiller</span>@else<span class="status-good">Disponible</span>@endif</td><td class="px-5 py-4 text-right"><a class="font-medium text-cyan-700 hover:text-cyan-900" href="{{ route('products.show', $product) }}">Ouvrir →</a></td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-16 text-center"><p class="font-medium text-slate-700">Aucun produit trouvé.</p><a href="{{ route('products.create') }}" class="mt-3 inline-block text-sm font-semibold text-cyan-700">Créer le premier produit →</a></td></tr>
            @endforelse
        </tbody></table></div>
        @if ($products->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $products->links() }}</div>@endif
    </section>
</x-app-layout>
