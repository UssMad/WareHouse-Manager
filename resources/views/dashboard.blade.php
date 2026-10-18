<x-app-layout>
    <div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="page-kicker">Vue d’ensemble / opérations</p>
            <h1 class="page-title mt-2">Le stock aujourd’hui.</h1>
            <p class="mt-2 text-sm text-slate-600">Gardez l’œil sur ce qui entre, ce qui sort et ce qui demande votre attention.</p>
        </div>
        <div class="flex flex-wrap gap-3"><a href="{{ route('exports.inventory') }}" class="btn-secondary">↓ Exporter Excel</a><a href="{{ route('products.create') }}" class="btn-primary">+ Ajouter un produit</a></div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="metric"><p class="metric-label">Références actives</p><p class="metric-value">{{ $productCount }}</p></div>
        <div class="metric"><p class="metric-label">Unités disponibles</p><p class="metric-value">{{ number_format($unitCount, 0, ',', ' ') }}</p></div>
        <div class="metric border-l-4 {{ $attentionCount ? 'border-l-amber-500' : 'border-l-emerald-500' }}"><p class="metric-label">À surveiller</p><p class="metric-value">{{ $attentionCount }}</p></div>
    </div>

    <div class="mt-7 grid gap-7 lg:grid-cols-[1.25fr_.75fr]">
        <section class="panel">
            <div class="panel-head flex items-center justify-between"><div><p class="page-kicker">Journal récent</p><h2 class="mt-1 text-lg font-semibold">Derniers mouvements</h2></div><a href="{{ route('products.index') }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Voir les produits →</a></div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-head"><tr><th class="px-5 py-3">Produit</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Quantité</th><th class="px-5 py-3">Date</th></tr></thead>
                    <tbody>
                        @forelse ($recentMovements as $movement)
                            <tr class="table-row"><td class="px-5 py-4 font-medium"><a class="hover:text-cyan-700" href="{{ route('products.show', $movement->product) }}">{{ $movement->product->name }}</a><span class="ml-2 font-mono text-xs text-slate-400">{{ $movement->product->reference }}</span></td><td class="px-5 py-4"><span class="{{ $movement->type === 'entry' ? 'status-good' : 'status-out' }}">{{ $movement->type === 'entry' ? 'Entrée' : 'Sortie' }}</span></td><td class="px-5 py-4 font-mono font-semibold">{{ $movement->type === 'entry' ? '+' : '−' }}{{ $movement->quantity }}</td><td class="px-5 py-4 text-slate-500">{{ $movement->occurred_at->format('d/m/Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-slate-500">Aucun mouvement pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><p class="page-kicker">Signal d’attention</p><h2 class="mt-1 text-lg font-semibold">Stock faible</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse ($lowProducts as $product)
                    <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50"><div><p class="font-medium text-slate-800">{{ $product->name }}</p><p class="mt-1 font-mono text-xs text-slate-500">{{ $product->category->name }} · {{ $product->reference }}</p></div><div class="text-right"><p class="font-mono text-lg font-semibold {{ $product->stock_quantity === 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $product->stock_quantity }}</p><p class="text-xs text-slate-500">seuil {{ $product->alert_threshold }}</p></div></a>
                @empty
                    <div class="px-5 py-12 text-center text-slate-500">Tout est sous contrôle.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
