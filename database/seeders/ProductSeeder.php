<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $shirts = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts',
            'description' => 'Premium woven shirts crafted from raw organic linen and fine cotton.',
            'image_path' => '/images/lookbook_linen.png',
        ]);

        $trousers = Category::create([
            'name' => 'Trousers',
            'slug' => 'trousers',
            'description' => 'Tailored pleated trousers and relaxed lounge pants engineered for form and ease.',
            'image_path' => '/images/lookbook_streetwear.png',
        ]);

        $outerwear = Category::create([
            'name' => 'Outerwear',
            'slug' => 'outerwear',
            'description' => 'Heavyweight overshirts, worker jackets, and structured canvas coats.',
            'image_path' => '/images/hero_slide_2.png',
        ]);

        $knitwear = Category::create([
            'name' => 'Knitwear',
            'slug' => 'knitwear',
            'description' => 'Soft textured organic cotton sweaters and lightweight cardigans.',
            'image_path' => '/images/hero_slide_3.png',
        ]);

        // 2. Create Products in Shirts
        $product1 = Product::create([
            'category_id' => $shirts->id,
            'name' => 'Linen Lounge Shirt',
            'slug' => 'linen-lounge-shirt',
            'description' => 'An easy-fitting lounge shirt woven in heavyweight premium linen. Features natural shell buttons, a relaxed camp collar, and chest pocket. Designed for ultimate comfort and ventilation in warm climates.',
            'base_price' => 85.00,
            'is_active' => true,
        ]);

        // Variants for Linen Lounge Shirt
        $colors = ['Oatmeal', 'Sage', 'Charcoal'];
        $sizes = ['S', 'M', 'L', 'XL'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product1->id,
                    'size' => $size,
                    'color' => $color,
                    'stock_quantity' => rand(15, 50),
                    'sku' => "linen-lounge-shirt-" . Str::slug($color) . "-" . Str::slug($size),
                    'additional_price' => $color === 'Charcoal' ? 5.00 : 0.00,
                ]);
            }
            // Add image matching the color
            ProductImage::create([
                'product_id' => $product1->id,
                'image_path' => '/images/lookbook_linen.png',
                'color' => $color,
                'is_primary' => $color === 'Oatmeal',
            ]);
        }

        $product2 = Product::create([
            'category_id' => $shirts->id,
            'name' => 'Studio Cotton Oxford',
            'slug' => 'studio-cotton-oxford',
            'description' => 'A classic tailored button-down oxford shirt cut from structured, heavy cotton cloth. Double-needle stitch construction with a clean, contemporary silhouette.',
            'base_price' => 75.00,
            'is_active' => true,
        ]);

        // Variants for Oxford Shirt
        $colors = ['White', 'Navy'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product2->id,
                    'size' => $size,
                    'color' => $color,
                    'stock_quantity' => rand(10, 30),
                    'sku' => "studio-oxford-" . Str::slug($color) . "-" . Str::slug($size),
                    'additional_price' => 0.00,
                ]);
            }
            ProductImage::create([
                'product_id' => $product2->id,
                'image_path' => '/images/hero_slide_1.png',
                'color' => $color,
                'is_primary' => $color === 'White',
            ]);
        }

        // 3. Create Products in Trousers
        $product3 = Product::create([
            'category_id' => $trousers->id,
            'name' => 'Pleated Linen Trousers',
            'slug' => 'pleated-linen-trousers',
            'description' => 'Relaxed pleated trousers with an elasticated waistband drawcord. Woven in structured linen-cotton blend that holds form while remaining breathable.',
            'base_price' => 95.00,
            'is_active' => true,
        ]);

        $colors = ['Oatmeal', 'Charcoal'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product3->id,
                    'size' => $size,
                    'color' => $color,
                    'stock_quantity' => rand(10, 40),
                    'sku' => "pleated-trousers-" . Str::slug($color) . "-" . Str::slug($size),
                    'additional_price' => 0.00,
                ]);
            }
            ProductImage::create([
                'product_id' => $product3->id,
                'image_path' => '/images/lookbook_linen.png',
                'color' => $color,
                'is_primary' => $color === 'Oatmeal',
            ]);
        }

        // 4. Create Products in Outerwear
        $product4 = Product::create([
            'category_id' => $outerwear->id,
            'name' => 'Worker Canvas Jacket',
            'slug' => 'worker-canvas-jacket',
            'description' => 'A durable chore coat tailored in heavyweight organic cotton canvas. Triple-stitched seams, three front patch pockets, and interior chest pocket. Finished with matte black metal rivets.',
            'base_price' => 140.00,
            'is_active' => true,
        ]);

        $colors = ['Navy', 'Sage'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product4->id,
                    'size' => $size,
                    'color' => $color,
                    'stock_quantity' => rand(5, 20),
                    'sku' => "worker-jacket-" . Str::slug($color) . "-" . Str::slug($size),
                    'additional_price' => 10.00,
                ]);
            }
            ProductImage::create([
                'product_id' => $product4->id,
                'image_path' => '/images/hero_slide_2.png',
                'color' => $color,
                'is_primary' => $color === 'Navy',
            ]);
        }
    }
}
