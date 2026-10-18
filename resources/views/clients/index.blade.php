<x-app-layout>
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="page-kicker">Répertoire / partenaires commerciaux</p><h1 class="page-title mt-2">Clients</h1><p class="mt-2 text-sm text-slate-600">Gardez les coordonnées, opérations et retours de chaque client au même endroit.</p></div>
        <div class="border-l-2 border-cyan-600 pl-4 text-sm text-slate-600"><span class="block font-mono text-xs uppercase tracking-[.12em] text-cyan-700">Principe</span>Un client blacklisté ne peut pas recevoir une sortie de stock.</div>
    </div>

    <div class="grid gap-7 xl:grid-cols-[.8fr_1.2fr]">
        <section class="form-shell self-start">
            <div class="form-rail"><p class="form-eyebrow">Nouveau contact</p><h2 class="mt-2 text-xl font-semibold">Ajouter un client</h2><p class="mt-2 text-sm leading-6 text-slate-300">Une référence unique permet de le retrouver immédiatement lors d’une sortie ou d’un retour.</p></div>
            <form method="POST" action="{{ route('clients.store') }}">@csrf
                <div class="form-section space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2"><div><label class="form-label" for="reference">Référence client</label><input class="form-input @error('reference') form-input-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}" placeholder="CLI-001" required autofocus>@error('reference')<p class="form-error">{{ $message }}</p>@enderror</div><div><label class="form-label" for="client_type">Type</label><select class="form-input @error('client_type') form-input-invalid @enderror" id="client_type" name="client_type" required><option value="company" @selected(old('client_type', 'company') === 'company')>Entreprise</option><option value="individual" @selected(old('client_type') === 'individual')>Client individuel</option></select>@error('client_type')<p class="form-error">{{ $message }}</p>@enderror</div></div>
                    <div><label class="form-label" for="name">Entreprise ou nom complet</label><input class="form-input @error('name') form-input-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Ex. Atlas Distribution" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div><label class="form-label" for="contact_name">Personne de contact <span class="font-normal text-slate-400">(facultatif)</span></label><input class="form-input @error('contact_name') form-input-invalid @enderror" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" placeholder="Ex. Youssef El Amrani">@error('contact_name')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-5 sm:grid-cols-2"><div><label class="form-label" for="phone">Téléphone <span class="font-normal text-slate-400">ou email</span></label><input class="form-input @error('phone') form-input-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="06 00 00 00 00">@error('phone')<p class="form-error">{{ $message }}</p>@enderror</div><div><label class="form-label" for="email">Email <span class="font-normal text-slate-400">ou téléphone</span></label><input class="form-input @error('email') form-input-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="contact@entreprise.ma">@error('email')<p class="form-error">{{ $message }}</p>@enderror</div></div>
                    <label class="flex cursor-pointer items-start gap-3 rounded border border-slate-200 bg-slate-50 p-4"><input class="mt-1 rounded border-slate-300 text-red-600 focus:ring-red-500" type="checkbox" name="is_blacklisted" value="1" @checked(old('is_blacklisted'))><span><span class="block text-sm font-semibold text-slate-800">Ajouter à la blacklist</span><span class="mt-1 block text-xs leading-5 text-slate-500">Les sorties de stock vers ce client seront bloquées.</span></span></label>
                </div>
                <div class="form-actionbar"><p class="text-xs text-slate-500">Téléphone ou email obligatoire.</p><button class="btn-primary">Ajouter le client</button></div>
            </form>
        </section>

        <section class="panel">
            <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[1fr_180px_auto]"><input class="form-input" name="search" value="{{ request('search') }}" placeholder="Nom, référence, téléphone ou email"><select class="form-input" name="status"><option value="">Tous les clients</option><option value="active" @selected(request('status') === 'active')>Clients actifs</option><option value="blacklisted" @selected(request('status') === 'blacklisted')>Blacklistés</option></select><button class="btn-secondary">Filtrer</button></form>
            <div class="panel-head"><p class="page-kicker">Répertoire actif</p><h2 class="mt-1 text-lg font-semibold">{{ $clients->total() }} client{{ $clients->total() > 1 ? 's' : '' }}</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($clients as $client)
                    <details class="group px-5 py-5" @if(old('client_id') == $client->id) open @endif>
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
                            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="font-semibold text-slate-800">{{ $client->name }}</p>@if($client->is_blacklisted)<span class="status-out">Blacklisté</span>@else<span class="status-good">Actif</span>@endif</div><p class="mt-1 font-mono text-xs text-slate-500">{{ $client->reference }} · {{ $client->client_type === 'company' ? 'Entreprise' : 'Client individuel' }}</p><p class="mt-2 truncate text-sm text-slate-500">{{ $client->email ?? $client->phone }}</p></div>
                            <div class="grid shrink-0 grid-cols-2 gap-x-4 text-right"><div><p class="font-mono text-lg font-semibold text-slate-800">{{ $client->movements_count }}</p><p class="text-[10px] uppercase tracking-wide text-slate-500">opérations</p></div><div><p class="font-mono text-lg font-semibold {{ $client->returns_count ? 'text-amber-600' : 'text-slate-700' }}">{{ $client->returns_count }}</p><p class="text-[10px] uppercase tracking-wide text-slate-500">retours</p></div></div>
                        </summary>
                        <div class="mt-5 border-t border-slate-100 pt-5">
                            <form id="client-update-{{ $client->id }}" method="POST" action="{{ route('clients.update', $client) }}" class="grid gap-4 sm:grid-cols-2">@csrf @method('PUT')
                                <div><label class="form-label" for="client-reference-{{ $client->id }}">Référence</label><input class="form-input" id="client-reference-{{ $client->id }}" name="reference" value="{{ $client->reference }}" required></div><div><label class="form-label" for="client-type-{{ $client->id }}">Type</label><select class="form-input" id="client-type-{{ $client->id }}" name="client_type"><option value="company" @selected($client->client_type === 'company')>Entreprise</option><option value="individual" @selected($client->client_type === 'individual')>Client individuel</option></select></div>
                                <div><label class="form-label" for="client-name-{{ $client->id }}">Entreprise ou nom</label><input class="form-input" id="client-name-{{ $client->id }}" name="name" value="{{ $client->name }}" required></div><div><label class="form-label" for="client-contact-{{ $client->id }}">Personne de contact</label><input class="form-input" id="client-contact-{{ $client->id }}" name="contact_name" value="{{ $client->contact_name }}"></div>
                                <div><label class="form-label" for="client-phone-{{ $client->id }}">Téléphone</label><input class="form-input" id="client-phone-{{ $client->id }}" name="phone" value="{{ $client->phone }}"></div><div><label class="form-label" for="client-email-{{ $client->id }}">Email</label><input class="form-input" id="client-email-{{ $client->id }}" name="email" value="{{ $client->email }}"></div>
                                <label class="flex items-center gap-3 sm:col-span-2"><input class="rounded border-slate-300 text-red-600 focus:ring-red-500" type="checkbox" name="is_blacklisted" value="1" @checked($client->is_blacklisted)><span class="text-sm font-medium text-slate-700">Client blacklisté : bloquer les sorties de stock.</span></label>
                            </form>
                            <div class="mt-4 flex flex-wrap gap-4"><button form="client-update-{{ $client->id }}" class="btn-secondary">Enregistrer les modifications</button><form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Supprimer le client « {{ $client->name }} » ? Les anciens mouvements seront conservés sans liaison client.')">@csrf @method('DELETE')<button class="text-sm font-medium text-red-600 hover:text-red-800">Supprimer le client</button></form></div>
                        </div>
                    </details>
                @empty
                    <div class="px-5 py-16 text-center"><p class="font-medium text-slate-700">Aucun client trouvé.</p><p class="mt-1 text-sm text-slate-500">Ajoutez votre premier client avec le formulaire à gauche.</p></div>
                @endforelse
            </div>
            @if($clients->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $clients->links() }}</div>@endif
        </section>
    </div>
</x-app-layout>
