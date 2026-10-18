<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InventoryExportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockMovementController;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $userId = request()->user()->id;

        return view('dashboard', [
            'productCount' => Product::where('user_id', $userId)->count(),
            'unitCount' => Product::where('user_id', $userId)->sum('stock_quantity'),
            'attentionCount' => Product::where('user_id', $userId)->whereColumn('stock_quantity', '<=', 'alert_threshold')->count(),
            'recentMovements' => StockMovement::where('user_id', $userId)->with('product')->latest('occurred_at')->take(5)->get(),
            'lowProducts' => Product::where('user_id', $userId)->whereColumn('stock_quantity', '<=', 'alert_threshold')->with('category')->orderBy('stock_quantity')->take(5)->get(),
        ]);
    })->name('dashboard');

    Route::get('/exports/inventory', InventoryExportController::class)->name('exports.inventory');

    Route::resource('products', ProductController::class);
    Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/archives', [ProductController::class, 'archived'])->name('products.archived');
    Route::patch('/archives/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::post('/products/{product}/movements', [StockMovementController::class, 'store'])->name('products.movements.store');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
