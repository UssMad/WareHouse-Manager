<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->where('user_id', $request->user()->id)
            ->withCount('movements')
            ->withCount(['movements as returns_count' => fn ($query) => $query->where('is_return', true)])
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($subQuery) => $subQuery
                ->where('name', 'like', '%'.$request->input('search').'%')
                ->orWhere('reference', 'like', '%'.$request->input('search').'%')
                ->orWhere('email', 'like', '%'.$request->input('search').'%')
                ->orWhere('phone', 'like', '%'.$request->input('search').'%')))
            ->when($request->status === 'blacklisted', fn ($query) => $query->where('is_blacklisted', true))
            ->when($request->status === 'active', fn ($query) => $query->where('is_blacklisted', false))
            ->orderBy('is_blacklisted')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);
        $request->user()->clients()->create($request->safe()->merge(['is_blacklisted' => $request->boolean('is_blacklisted')])->all());

        return redirect()->route('clients.index')->with('success', 'Client ajouté au répertoire.');
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->safe()->merge(['is_blacklisted' => $request->boolean('is_blacklisted')])->all());

        return redirect()->route('clients.index')->with('success', 'Fiche client mise à jour.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé du répertoire.');
    }
}
