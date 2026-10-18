<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()->where('user_id', $request->user()->id)->with('category')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->input('search').'%')
                ->orWhere('reference', 'like', '%'.$request->input('search').'%')))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->status === 'low', fn ($query) => $query->whereColumn('stock_quantity', '<=', 'alert_threshold'))
            ->when($request->status === 'available', fn ($query) => $query->whereColumn('stock_quantity', '>', 'alert_threshold'))
            ->latest()->paginate(10)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Product::class);

        return view('products.create', ['categories' => Category::where('user_id', $request->user()->id)->orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = DB::transaction(function () use ($request) {
            $product = $request->user()->products()->create($request->safe()->all());

            if ($product->stock_quantity > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'type' => 'entry',
                    'quantity' => $product->stock_quantity,
                    'reason' => 'Stock initial',
                    'occurred_at' => now(),
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)->with('success', 'Produit ajouté au stock.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);
        $product->load('category');
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->with(['user:id,name', 'client:id,reference,name,is_blacklisted'])
            ->latest('occurred_at')
            ->paginate(12);

        $clients = Client::query()
            ->where('user_id', request()->user()->id)
            ->where('is_blacklisted', false)
            ->orderBy('name')
            ->get(['id', 'reference', 'name', 'client_type']);

        return view('products.show', compact('product', 'movements', 'clients'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::where('user_id', request()->user()->id)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->all());

        return redirect()->route('products.show', $product)->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit archivé.');
    }

    public function archived(Request $request): View
    {
        $products = Product::onlyTrashed()->where('user_id', $request->user()->id)->with('category')->latest('deleted_at')->paginate(10);

        return view('products.archived', compact('products'));
    }

    public function restore(Request $request, int $product): RedirectResponse
    {
        $product = Product::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($product);
        $this->authorize('restore', $product);
        $product->restore();

        return redirect()->route('products.archived')->with('success', 'Produit restauré.');
    }
}
