<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AccountPaymentMethodController;
use App\Http\Controllers\AccountShippingAddressController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDiscountCodeController;
use App\Http\Controllers\Admin\AdminLegalContactRequestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PayPalCheckoutController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeCheckoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/language', [LanguageController::class, 'switch'])->name('language.switch');

Route::redirect('/', '/products');

Route::view('/voidforge-info', 'welcome')->name('home');
Route::get('/robots.txt', function () {
    return response(view('robots'), 200, ['Content-Type' => 'text/plain']);
});

Route::view('/privacy-policy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms-and-conditions', 'legal.terms')->name('legal.terms');
Route::view('/returns-and-refunds', 'legal.returns')->name('legal.returns');
Route::view('/shipping-and-delivery', 'legal.shipping')->name('legal.shipping');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');
Route::get('/order-tracking', [OrderTrackingController::class, 'index'])->name('order.tracking');
Route::post('/order-tracking', [OrderTrackingController::class, 'search'])->middleware('throttle:10,5')->name('order.tracking.search');

Route::get('/media/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*')->name('media.show');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/discount', [CartController::class, 'applyDiscount'])->name('cart.discount.store');
Route::delete('/cart/discount', [CartController::class, 'removeDiscount'])->name('cart.discount.destroy');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/shipping', [CheckoutController::class, 'index'])->middleware('cart.not_empty')->name('checkout.index');
Route::get('/checkout', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/checkout/complete', [CheckoutController::class, 'complete'])->name('checkout.complete');
Route::get('/checkout/complete/download', [CheckoutController::class, 'download'])->name('checkout.download');
Route::post('/shipping', [CheckoutController::class, 'store'])->middleware('cart.not_empty')->name('checkout.store');
Route::post('/shipping/back', [CheckoutController::class, 'back'])->name('checkout.back');
Route::post('/shipping/address', [CheckoutController::class, 'selectAddress'])->middleware('auth')->name('checkout.address');
Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{order}/stripe', [StripeCheckoutController::class, 'store'])->middleware('throttle:checkout.payment-start')->name('stripe.checkout.store');
Route::post('/checkout/{order}/paypal', [PayPalCheckoutController::class, 'store'])->middleware('throttle:checkout.payment-start')->name('paypal.checkout.store');
Route::get('/checkout/paypal/return', [PayPalCheckoutController::class, 'complete'])->name('paypal.checkout.return');
Route::get('/checkout/{order}/paypal/cancel', [PayPalCheckoutController::class, 'cancel'])->name('paypal.checkout.cancel');
Route::post('/webhooks/stripe', [StripeCheckoutController::class, 'webhook'])->name('stripe.webhook');
Route::post('/webhooks/paypal', [PayPalCheckoutController::class, 'webhook'])->name('paypal.webhook');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:auth.register');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', DashboardController::class)->name('dashboard');
    Route::delete('/account', [DashboardController::class, 'destroy'])->name('account.destroy');
    Route::get('/account/addresses', [AccountShippingAddressController::class, 'index'])->name('account.addresses.index');
    Route::post('/account/addresses', [AccountShippingAddressController::class, 'store'])->middleware('throttle:account.addresses.store')->name('account.addresses.store');
    Route::get('/account/addresses/{shippingAddress}/edit', [AccountShippingAddressController::class, 'edit'])->name('account.addresses.edit');
    Route::patch('/account/addresses/{shippingAddress}', [AccountShippingAddressController::class, 'update'])->name('account.addresses.update');
    Route::patch('/account/addresses/{shippingAddress}/default', [AccountShippingAddressController::class, 'makeDefault'])->name('account.addresses.default');
    Route::delete('/account/addresses/{shippingAddress}', [AccountShippingAddressController::class, 'destroy'])->name('account.addresses.destroy');
    Route::get('/account/payment-methods', [AccountPaymentMethodController::class, 'index'])->name('account.payment-methods.index');
    Route::post('/account/payment-methods', [AccountPaymentMethodController::class, 'store'])->middleware('throttle:account.payment-methods.store')->name('account.payment-methods.store');
    Route::patch('/account/payment-methods/{paymentMethod}/default', [AccountPaymentMethodController::class, 'makeDefault'])->name('account.payment-methods.default');
    Route::delete('/account/payment-methods/{paymentMethod}', [AccountPaymentMethodController::class, 'destroy'])->name('account.payment-methods.destroy');
    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderReference}', [OrderHistoryController::class, 'show'])->middleware('order.owner_or_admin')->name('orders.show');
    Route::get('/orders/{orderReference}/download', [OrderHistoryController::class, 'download'])->middleware('order.owner_or_admin')->name('orders.download');
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
    Route::get('/discount-codes', [AdminDiscountCodeController::class, 'index'])->name('discount-codes.index');
    Route::get('/discount-codes/create', [AdminDiscountCodeController::class, 'create'])->name('discount-codes.create');
    Route::post('/discount-codes', [AdminDiscountCodeController::class, 'store'])->name('discount-codes.store');
    Route::get('/discount-codes/{discountCode}/edit', [AdminDiscountCodeController::class, 'edit'])->name('discount-codes.edit');
    Route::patch('/discount-codes/{discountCode}', [AdminDiscountCodeController::class, 'update'])->name('discount-codes.update');
    Route::patch('/discount-codes/{discountCode}/archive', [AdminDiscountCodeController::class, 'archive'])->name('discount-codes.archive');
    Route::patch('/discount-codes/{discountCode}/restore', [AdminDiscountCodeController::class, 'restore'])->name('discount-codes.restore');
    Route::get('/legal-requests', [AdminLegalContactRequestController::class, 'index'])->name('legal-requests.index');
    Route::patch('/legal-requests/{legalContactRequest}/resolve', [AdminLegalContactRequestController::class, 'resolve'])->name('legal-requests.resolve');
    Route::patch('/legal-requests/{legalContactRequest}/reopen', [AdminLegalContactRequestController::class, 'reopen'])->name('legal-requests.reopen');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{order}/restore', [AdminOrderController::class, 'restore'])->name('orders.restore');
});
