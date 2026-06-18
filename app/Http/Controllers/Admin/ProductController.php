<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with('category', 'variants', 'images')->latest()->get();
        return Inertia::render('Admin/Products/Index', [
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::all();
        
        // Auto-seed default categories if empty to keep demo fully functional
        if ($categories->isEmpty()) {
            $categories = collect([
                Category::create(['name' => 'Shirts', 'slug' => 'shirts', 'description' => 'Premium shirts']),
                Category::create(['name' => 'Trousers', 'slug' => 'trousers', 'description' => 'Tailored trousers']),
                Category::create(['name' => 'Outerwear', 'slug' => 'outerwear', 'description' => 'Luxury outerwear']),
                Category::create(['name' => 'Knitwear', 'slug' => 'knitwear', 'description' => 'Cozy knitwear']),
            ]);
        }

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created product with its variants and images.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'color_images' => 'nullable|array',
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string|max:50',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.sku' => 'nullable|string|unique:product_variants,sku',
            'variants.*.additional_price' => 'required|numeric|min:0',
        ]);

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
            'is_active' => true,
        ]);

        // Process variants
        foreach ($validated['variants'] as $variantData) {
            $product->variants()->create([
                'size' => $variantData['size'],
                'color' => $variantData['color'],
                'stock_quantity' => $variantData['stock_quantity'],
                'sku' => $variantData['sku'] ?: ($product->slug . '-' . Str::slug($variantData['color']) . '-' . Str::slug($variantData['size'])),
                'additional_price' => $variantData['additional_price'],
            ]);
        }

        // Process color-grouped images
        if ($request->file('color_images')) {
            $isFirstImage = true;
            foreach ($request->file('color_images') as $color => $files) {
                if (is_array($files)) {
                    foreach ($files as $index => $file) {
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_path' => '/storage/' . $path,
                            'color' => $color === 'general_unspecified' ? null : $color,
                            'is_primary' => $isFirstImage,
                        ]);
                        $isFirstImage = false;
                    }
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product, variants and images created successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = Product::with(['variants', 'images', 'category'])->findOrFail($id);
        $categories = Category::all();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'color_images' => 'nullable|array',
            'existing_image_colors' => 'nullable|array',
            'existing_image_colors.*' => 'nullable|string',
            'deleted_image_ids' => 'nullable|array',
            'deleted_image_ids.*' => 'integer',
            'base_price' => 'required|numeric|min:0',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.size' => 'required|string|max:50',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.sku' => 'nullable|string',
            'variants.*.additional_price' => 'required|numeric|min:0',
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
        ]);

        // Process variants: track ids of saved/updated variants to delete missing ones
        $savedVariantIds = [];

        foreach ($validated['variants'] as $variantData) {
            $sku = $variantData['sku'] ?: ($product->slug . '-' . Str::slug($variantData['color']) . '-' . Str::slug($variantData['size']));
            
            if (!empty($variantData['id'])) {
                // Update existing variant
                $variant = $product->variants()->findOrFail($variantData['id']);
                $variant->update([
                    'size' => $variantData['size'],
                    'color' => $variantData['color'],
                    'stock_quantity' => $variantData['stock_quantity'],
                    'sku' => $sku,
                    'additional_price' => $variantData['additional_price'],
                ]);
                $savedVariantIds[] = $variant->id;
            } else {
                // Create new variant
                $newVariant = $product->variants()->create([
                    'size' => $variantData['size'],
                    'color' => $variantData['color'],
                    'stock_quantity' => $variantData['stock_quantity'],
                    'sku' => $sku,
                    'additional_price' => $variantData['additional_price'],
                ]);
                $savedVariantIds[] = $newVariant->id;
            }
        }

        // Delete variants that were removed in the UI
        $product->variants()->whereNotIn('id', $savedVariantIds)->delete();

        // Process deleted images
        if (!empty($validated['deleted_image_ids'])) {
            $imagesToDelete = $product->images()->whereIn('id', $validated['deleted_image_ids'])->get();
            foreach ($imagesToDelete as $img) {
                $filePath = str_replace('/storage/', '', $img->image_path);
                Storage::disk('public')->delete($filePath);
                $img->delete();
            }
        }

        // Update colors for existing images
        if (!empty($validated['existing_image_colors'])) {
            foreach ($validated['existing_image_colors'] as $imgId => $color) {
                $product->images()->where('id', $imgId)->update([
                    'color' => $color === 'general_unspecified' ? null : $color
                ]);
            }
        }

        // Process new color-grouped images
        if ($request->file('color_images')) {
            foreach ($request->file('color_images') as $color => $files) {
                if (is_array($files)) {
                    foreach ($files as $index => $file) {
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_path' => '/storage/' . $path,
                            'color' => $color === 'general_unspecified' ? null : $color,
                            'is_primary' => !$product->images()->where('is_primary', true)->exists() && $index === 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product and variants updated successfully.');
    }
}
