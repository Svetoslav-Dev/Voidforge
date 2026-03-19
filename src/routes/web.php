<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AccountPaymentMethodController;
use App\Http\Controllers\AccountShippingAddressController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\PayPalCheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeCheckoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::redirect('/', '/products');

Route::view('/voidforge-info', 'welcome')->name('home');

Route::get('/media/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('media.show');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/shipping', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/checkout/complete', [CheckoutController::class, 'complete'])->name('checkout.complete');
Route::get('/checkout/complete/download', [CheckoutController::class, 'download'])->name('checkout.download');
Route::post('/shipping', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/shipping/back', [CheckoutController::class, 'back'])->name('checkout.back');
Route::post('/shipping/address', [CheckoutController::class, 'selectAddress'])->middleware('auth')->name('checkout.address');
Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{order}/stripe', [StripeCheckoutController::class, 'store'])->name('stripe.checkout.store');
Route::post('/checkout/{order}/paypal', [PayPalCheckoutController::class, 'store'])->name('paypal.checkout.store');
Route::get('/checkout/paypal/return', [PayPalCheckoutController::class, 'complete'])->name('paypal.checkout.return');
Route::get('/checkout/{order}/paypal/cancel', [PayPalCheckoutController::class, 'cancel'])->name('paypal.checkout.cancel');
Route::post('/webhooks/stripe', [StripeCheckoutController::class, 'webhook'])->name('stripe.webhook');
Route::post('/webhooks/paypal', [PayPalCheckoutController::class, 'webhook'])->name('paypal.webhook');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/account', DashboardController::class)->name('dashboard');
    Route::delete('/account', [DashboardController::class, 'destroy'])->name('account.destroy');
    Route::get('/account/addresses', [AccountShippingAddressController::class, 'index'])->name('account.addresses.index');
    Route::post('/account/addresses', [AccountShippingAddressController::class, 'store'])->name('account.addresses.store');
    Route::get('/account/addresses/{shippingAddress}/edit', [AccountShippingAddressController::class, 'edit'])->name('account.addresses.edit');
    Route::patch('/account/addresses/{shippingAddress}', [AccountShippingAddressController::class, 'update'])->name('account.addresses.update');
    Route::patch('/account/addresses/{shippingAddress}/default', [AccountShippingAddressController::class, 'makeDefault'])->name('account.addresses.default');
    Route::delete('/account/addresses/{shippingAddress}', [AccountShippingAddressController::class, 'destroy'])->name('account.addresses.destroy');
    Route::get('/account/payment-methods', [AccountPaymentMethodController::class, 'index'])->name('account.payment-methods.index');
    Route::post('/account/payment-methods', [AccountPaymentMethodController::class, 'store'])->name('account.payment-methods.store');
    Route::patch('/account/payment-methods/{paymentMethod}/default', [AccountPaymentMethodController::class, 'makeDefault'])->name('account.payment-methods.default');
    Route::delete('/account/payment-methods/{paymentMethod}', [AccountPaymentMethodController::class, 'destroy'])->name('account.payment-methods.destroy');
    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderReference}', [OrderHistoryController::class, 'show'])->name('orders.show');
    Route::get('/orders/{orderReference}/download', [OrderHistoryController::class, 'download'])->name('orders.download');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminPanelController::class)->name('panel');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/restore', [AdminProductController::class, 'restore'])->name('products.restore');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/categories/{category}/restore', [AdminCategoryController::class, 'restore'])->name('categories.restore');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{order}/restore', [AdminOrderController::class, 'restore'])->name('orders.restore');
});
