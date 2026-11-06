<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('shop.home');
Route::get('/products', [StorefrontController::class, 'index'])->name('shop.index');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('shop.products.show');

Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
Route::post('/favorites/{product}', [FavoriteController::class, 'toggle'])->block()->name('favorites.toggle');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'store'])->block()->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->block()->name('cart.update');
Route::delete('/cart/{productId}', [CartController::class, 'destroy'])->whereNumber('productId')->block()->name('cart.destroy');
Route::get('/checkout', [CheckoutController::class, 'show'])->block()->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->block()->name('checkout.store');
Route::get('/orders/{reference}', [CheckoutController::class, 'receipt'])
    ->where('reference', '[0-9A-HJKMNP-TV-Z]{26}')->name('orders.receipt');

Route::get('/login', [LoginController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware(['guest', 'throttle:admin-login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->as('admin.')->middleware(['auth', 'can:manage-catalog'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('subcategories', SubcategoryController::class)->except('show');
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
});
