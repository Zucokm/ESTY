<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Product;

Route::get('/', function () {
    $products = Product::with(['category', 'variants', 'images' => function ($query) {
        $query->where('is_primary', true);
    }])->where('is_active', true)->latest()->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'products' => $products,
    ]);
});

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Models\Order;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        $orders = Order::with('user')->latest()->take(10)->get();
        return Inertia::render('Dashboard', [
            'orders' => $orders
        ]);
    })->name('dashboard');

    Route::resource('admin/products', ProductController::class);
    Route::get('admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::put('admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
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
