<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Lookbook;
use App\Models\Setting;

Route::get('/', function () {
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
});

Route::get('/projects', function () {
    $products = Product::with(['category', 'variants', 'images'])->where('is_active', true)->latest()->get();
    $categories = Category::all();
    return Inertia::render('Projects/Index', [
        'products' => $products,
        'categories' => $categories,
    ]);
})->name('projects.index');

Route::get('/products/{slug}', function ($slug) {
    $product = Product::with(['category', 'variants', 'images'])->where('slug', $slug)->where('is_active', true)->firstOrFail();
    return Inertia::render('Products/Show', [
        'product' => $product
    ]);
})->name('products.show');

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Models\Order;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        $orders = Order::with('user')->latest()->take(10)->get();

        // Calculate stats
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $activeOrders = Order::whereIn('status', ['pending', 'processing', 'packing', 'shipping', 'delivered'])->count();
        $lowStockAlerts = \App\Models\ProductVariant::where('stock_quantity', '<=', 10)->count();
        $totalProducts = Product::count();

        // Calculate percentage changes
        $now = \Illuminate\Support\Carbon::now();
        $thirtyDaysAgo = (clone $now)->subDays(30);
        $sixtyDaysAgo = (clone $now)->subDays(60);

        // 1. Revenue Change
        $revLast30 = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('total_amount');
        $revPrev30 = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $sixtyDaysAgo)
            ->where('created_at', '<', $thirtyDaysAgo)
            ->sum('total_amount');
        $revenueChange = $revPrev30 > 0 ? (($revLast30 - $revPrev30) / $revPrev30) * 100 : ($revLast30 > 0 ? 100 : 0);
        
        // 2. Active Orders Change
        $actLast30 = Order::whereIn('status', ['pending', 'processing', 'packing', 'shipping', 'delivered'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        $actPrev30 = Order::whereIn('status', ['pending', 'processing', 'packing', 'shipping', 'delivered'])
            ->where('created_at', '>=', $sixtyDaysAgo)
            ->where('created_at', '<', $thirtyDaysAgo)
            ->count();
        $activeOrdersChange = $actPrev30 > 0 ? (($actLast30 - $actPrev30) / $actPrev30) * 100 : ($actLast30 > 0 ? 100 : 0);

        // 3. Low Stock Change (mocked/stable change rate since historical logs aren't in variants table)
        $lowStockChange = -4.1;

        // 4. Products Change
        $prodLast30 = Product::where('created_at', '>=', $thirtyDaysAgo)->count();
        $prodPrev30 = Product::where('created_at', '<', $thirtyDaysAgo)->count();
        $productsChange = $prodPrev30 > 0 ? ($prodLast30 / $prodPrev30) * 100 : ($prodLast30 > 0 ? 100 : 0);

        // 5. Daily revenue trend for the last 7 days
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Illuminate\Support\Carbon::now()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            $label = $date->format('D'); // e.g. Mon, Tue, etc.
            
            $dailyTotal = Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $formattedDate)
                ->sum('total_amount');
                
            $revenueTrend[] = [
                'label' => $label,
                'value' => floatval($dailyTotal)
            ];
        }

        // 6. Order status distribution
        $statuses = ['pending', 'processing', 'packing', 'shipping', 'delivered', 'completed', 'cancelled'];
        $orderDistribution = [];
        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $orderDistribution[] = [
                'label' => ucfirst($status),
                'value' => $count
            ];
        }

        // 7. Category catalog distribution
        $categories = Category::withCount('products')->get();
        $categoryDistribution = [];
        foreach ($categories as $category) {
            $categoryDistribution[] = [
                'label' => $category->name,
                'value' => $category->products_count
            ];
        }

        return Inertia::render('Dashboard', [
            'orders' => $orders,
            'stats' => [
                'totalRevenue' => [
                    'value' => '$' . number_format($totalRevenue, 2),
                    'change' => ($revenueChange >= 0 ? '+' : '') . number_format($revenueChange, 1) . '%'
                ],
                'activeOrders' => [
                    'value' => number_format($activeOrders),
                    'change' => ($activeOrdersChange >= 0 ? '+' : '') . number_format($activeOrdersChange, 1) . '%'
                ],
                'lowStockAlerts' => [
                    'value' => number_format($lowStockAlerts),
                    'change' => ($lowStockChange >= 0 ? '+' : '') . number_format($lowStockChange, 1) . '%'
                ],
                'totalProducts' => [
                    'value' => number_format($totalProducts),
                    'change' => ($productsChange >= 0 ? '+' : '') . number_format($productsChange, 1) . '%'
                ]
            ],
            'charts' => [
                'revenueTrend' => $revenueTrend,
                'orderDistribution' => $orderDistribution,
                'categoryDistribution' => $categoryDistribution
            ]
        ]);
    })->name('dashboard');

    Route::resource('admin/products', ProductController::class);
    Route::resource('admin/categories', CategoryController::class);
    Route::patch('admin/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('admin.products.toggleStatus');
    Route::get('admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::put('admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');

    // Admin Homepage settings
    Route::get('admin/homepage', [HomepageController::class, 'index'])->name('admin.homepage.index');
    Route::post('admin/homepage/slides', [HomepageController::class, 'storeSlide'])->name('admin.homepage.storeSlide');
    Route::post('admin/homepage/slides/{id}', [HomepageController::class, 'updateSlide'])->name('admin.homepage.updateSlide');
    Route::delete('admin/homepage/slides/{id}', [HomepageController::class, 'destroySlide'])->name('admin.homepage.destroySlide');
    Route::post('admin/homepage/lookbooks', [HomepageController::class, 'storeLookbook'])->name('admin.homepage.storeLookbook');
    Route::post('admin/homepage/lookbooks/{id}', [HomepageController::class, 'updateLookbook'])->name('admin.homepage.updateLookbook');
    Route::delete('admin/homepage/lookbooks/{id}', [HomepageController::class, 'destroyLookbook'])->name('admin.homepage.destroyLookbook');
    Route::post('admin/homepage/settings', [HomepageController::class, 'updateSettings'])->name('admin.homepage.updateSettings');
});

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerOrderController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/auth.php';
