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
            return Product::with(['category', 'variants', 'images'])->where('is_active', true)->latest()->get()->values()->toArray();
        });
        $categories = Cache::rememberForever('esty_all_categories', function () {
            return Category::all()->toArray();
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
        $product = Product::with(['category', 'variants', 'images', 'reviews' => function ($query) {
                $query->with('user')->latest();
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Calculate average rating
        $product->average_rating = $product->reviews->avg('rating');
        $product->reviews_count = $product->reviews->count();

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }
}
