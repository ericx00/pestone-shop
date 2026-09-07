<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::purchasable()->with('brand', 'category')
            ->where('is_featured', true)->latest()->take(8)->get();

        if ($featured->isEmpty()) {
            $featured = Product::purchasable()->with('brand', 'category')
                ->where('stock_status', 'in_stock')->inRandomOrder()->take(8)->get();
        }

        $deals = Product::purchasable()->with('brand', 'category')
            ->whereHas('offers', fn ($q) => $q->running())
            ->take(8)->get();

        $categories = Category::topLevel()->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('position')->orderBy('name')->get();

        $brands = Brand::has('products')->orderBy('name')->get();

        return view('home', compact('featured', 'deals', 'categories', 'brands'));
    }
}
