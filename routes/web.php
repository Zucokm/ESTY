<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [CatalogController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('products.show');

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ContactController;

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('admin/products', ProductController::class)->names('admin.products');
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

    Route::get('admin/contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('admin.contacts.index');
    Route::put('admin/contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'updateStatus'])->name('admin.contacts.update');
    // Admin Reportsn    Route::get('admin/reports/export-orders', [\App\Http\Controllers\Admin\ReportController::class, 'exportOrders'])->name('admin.reports.export');n
    // Admin Inventoryn    Route::get('admin/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('admin.inventory.index');n
    // Admin Reviewsn    Route::get('admin/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews.index');n    Route::put('admin/reviews/{review}/toggle-approval', [\App\Http\Controllers\Admin\ReviewController::class, 'toggleApproval'])->name('admin.reviews.toggle-approval');n    Route::delete('admin/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('admin.reviews.destroy');n
    // Admin Customersn    Route::get('admin/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers.index');n    Route::put('admin/customers/{user}/toggle-ban', [\App\Http\Controllers\Admin\CustomerController::class, 'toggleBan'])->name('admin.customers.toggle-ban');n
    // Admin Coupons
    Route::get('admin/coupons', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('admin.coupons.index');
    Route::post('admin/coupons', [\App\Http\Controllers\Admin\CouponController::class, 'store'])->name('admin.coupons.store');
    Route::put('admin/coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'update'])->name('admin.coupons.update');
    Route::delete('admin/coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('admin.coupons.destroy');
});

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\WishlistController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

require __DIR__.'/auth.php';
