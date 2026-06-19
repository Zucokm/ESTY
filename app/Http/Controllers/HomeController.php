<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Lookbook;
use App\Models\Setting;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Render the landing homepage.
     */
    public function index(): Response
    {
        $products = Product::with(['category', 'variants', 'images'])->where('is_active', true)->latest()->get();
        $categories = Category::all();
        
        $heroSlides = HeroSlide::where('is_active', true)->orderBy('sort_order')->get();
        $lookbooks = Lookbook::where('is_active', true)->orderBy('sort_order')->get();
        
        // Pluck settings
        $settingsRaw = Setting::all();
        $settings = [];
        foreach ($settingsRaw as $setting) {
            $settings[$setting->key] = $setting->value;
        }
        
        $defaults = [
            'brand_story_title' => 'Crafting a Dialogue Between Material & Form.',
            'brand_story_text_1' => 'At ESTY, we believe garments should not simply be worn, but experienced.',
            'brand_story_text_2' => 'Every buttonhole, every shoulder drape, and every single stitch is carefully planned and executed.',
            'brand_story_stat_1_val' => '100%',
            'brand_story_stat_1_lbl' => 'Local Sourcing',
            'brand_story_stat_2_val' => 'Limited',
            'brand_story_stat_2_lbl' => 'Studio Run',
            'brand_story_image' => '/images/brand_story.png',
        ];
        $settings = array_merge($defaults, $settings);

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'products' => $products,
            'categories' => $categories,
            'heroSlides' => $heroSlides,
            'lookbooks' => $lookbooks,
            'settings' => $settings,
        ]);
    }
}
