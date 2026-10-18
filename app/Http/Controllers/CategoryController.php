<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::where('user_id', $request->user()->id)->withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $request->user()->categories()->create($request->validated());

        return back()->with('success', 'Catégorie ajoutée.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'Cette catégorie contient encore des produits.');
        }

        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
