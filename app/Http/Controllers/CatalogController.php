<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    /**
     * Display the products catalog list page.
     */
    public function index(): Response
    {
        $products = Cache::rememberForever('esty_active_products', function () {
            return Product::with(['category', 'variants', 'images'])->where('is_active', true)->latest()->get();
        });
        $categories = Cache::rememberForever('esty_all_categories', function () {
            return Category::all();
        });
        
        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Display a specific product details page.
     */
    public function show(string $slug): Response
    {
        $product = Product::with(['category', 'variants', 'images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }
}
