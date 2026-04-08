<?php

use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\DigitalMenuController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\MediaController;
use App\Http\Controllers\Front\MyOrderController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Demo\SalesDemoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products/{product}', [HomeController::class, 'show'])->name('products.show');
Route::get('/sales-demo', [SalesDemoController::class, 'index'])
    ->withoutMiddleware([
        \App\Http\Middleware\AuthenticateSessionFingerprint::class,
        \App\Http\Middleware\EnforceSessionAbsoluteTimeout::class,
        \App\Http\Middleware\StoreAuditLog::class,
    ])
    ->name('sales.demo');

Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

/* المنيو الإلكتروني العام للعملاء - بدون تسجيل دخول */
Route::get('/digital-menu/{slug}', [DigitalMenuController::class, 'show'])->name('digital.menu.show');
Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->middleware('throttle:cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/update/{cartKey}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/remove/{cartKey}', [CartController::class, 'remove'])->name('cart.remove');
});

/*
|--------------------------------------------------------------------------
| Checkout Routes
|--------------------------------------------------------------------------
*/
Route::get('/checkout/method', [CheckoutController::class, 'method'])->name('checkout.method');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])
    ->middleware('throttle:checkout-coupon')
    ->name('checkout.apply-coupon');
Route::get('/checkout/verify-phone', [CheckoutController::class, 'showOtpVerificationPage'])
    ->name('checkout.otp.page');
Route::post('/checkout/verify-phone', [CheckoutController::class, 'verifyOtpAndContinue'])
    ->middleware('throttle:checkout-otp-verify')
    ->name('checkout.otp.verify');
Route::post('/checkout/verify-phone/resend', [CheckoutController::class, 'resendOtp'])
    ->middleware('throttle:checkout-otp-send')
    ->name('checkout.otp.resend');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:checkout-store')
    ->name('checkout.store');
Route::get('/order-success/{order}/{token?}', [CheckoutController::class, 'success'])->name('order.success');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [MyOrderController::class, 'index'])->name('my.orders');
    Route::get('/my-orders/{order}', [MyOrderController::class, 'show'])->name('my.orders.show');
    Route::post('/my-orders/{order}/cancel', [MyOrderController::class, 'cancel'])->name('my.orders.cancel');
    Route::post('/my-orders/{order}/reorder', [MyOrderController::class, 'reorder'])->name('my.orders.reorder');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Dashboard Fallback
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');
