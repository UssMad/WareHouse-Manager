<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->merge(['is_return' => $request->boolean('is_return')])->all();

        if ($data['type'] === 'exit' && $data['quantity'] > $product->stock_quantity) {
            return back()->withInput()->withErrors(['quantity' => 'La quantité de sortie dépasse le stock disponible.']);
        }

        DB::transaction(function () use ($data, $product, $request) {
            StockMovement::create($data + [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
            ]);

            $product->update([
                'stock_quantity' => $data['type'] === 'entry'
                    ? $product->stock_quantity + $data['quantity']
                    : $product->stock_quantity - $data['quantity'],
            ]);
        });

        return redirect()->route('products.show', $product)->with('success', 'Mouvement de stock enregistré.');
    }
}
