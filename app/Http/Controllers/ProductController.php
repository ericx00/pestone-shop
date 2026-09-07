<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ?Category $category = null, ?Brand $brand = null)
    {
        $query = Product::purchasable()->with('brand', 'category');

        if ($category) {
            $query->whereIn('category_id', $category->descendantIds());
        }
        if ($brand) {
            $query->where('brand_id', $brand->id);
        }

        if ($request->filled('q')) {
            $query->search($request->string('q'));
        }
        if ($request->filled('brands')) {
            $query->whereIn('brand_id', (array) $request->input('brands'));
        }
        if ($request->filled('min')) {
            $query->where('price', '>=', (int) $request->input('min'));
        }
        if ($request->filled('max')) {
            $query->where('price', '<=', (int) $request->input('max'));
        }
        if ($request->boolean('in_stock')) {
            $query->where('stock_qty', '>', 0);
        }

        $query->when($request->input('sort'), function ($q, $sort) {
            match ($sort) {
                'price_asc' => $q->orderBy('price'),
                'price_desc' => $q->orderByDesc('price'),
                'name' => $q->orderBy('name'),
                default => $q->orderByDesc('is_featured')->orderByDesc('id'),
            };
        }, fn ($q) => $q->orderByDesc('is_featured')->orderByDesc('id'));

        $products = $query->paginate(24)->withQueryString();

        // facets
        $brandScope = Product::purchasable();
        if ($category) {
            $brandScope->whereIn('category_id', $category->descendantIds());
        }
        $facetBrands = Brand::whereIn('id', (clone $brandScope)->distinct()->pluck('brand_id')->filter())
            ->orderBy('name')->get();

        $childCategories = $category
            ? $category->children()->where('is_active', true)->withCount('products')->get()
            : Category::topLevel()->where('is_active', true)->orderBy('name')->get();

        return view('shop.index', [
            'products' => $products,
            'category' => $category,
            'brand' => $brand,
            'facetBrands' => $facetBrands,
            'childCategories' => $childCategories,
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('brand', 'category.parent');

        $related = Product::purchasable()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with('brand')
            ->inRandomOrder()->take(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
