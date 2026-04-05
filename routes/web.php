<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductOptionGroupController;
use App\Http\Controllers\Admin\ProductOptionItemController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DigitalMenuSettingController;
use App\Http\Controllers\Admin\DigitalMenuCategoryController;
use App\Http\Controllers\Admin\DigitalMenuItemController;
use App\Http\Controllers\Admin\DigitalMenuQrController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\MyOrderController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\DigitalMenuController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Admin\PopupCampaignController;
use App\Http\Controllers\Admin\DeliveryDashboardController;
use App\Http\Controllers\Admin\DeliveryOrderController;
use App\Http\Controllers\Admin\DeliveryManagementController;

use App\Http\Controllers\Admin\ReportController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products/{product}', [HomeController::class, 'show'])->name('products.show');

Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

/* المنيو الإلكتروني العام للعملاء - بدون تسجيل دخول */
Route::get('/digital-menu/{slug}', [DigitalMenuController::class, 'show'])->name('digital.menu.show');

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

Route::prefix('cart')->group(function () {
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
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
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

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('permission:view_reports')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('admin.reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.export.pdf');
});
Route::middleware('permission:view_reports')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
});
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/poll', [DashboardController::class, 'poll'])->name('admin.dashboard.poll');

    Route::get('/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
Route::get('/popup-campaign', [PopupCampaignController::class, 'edit'])->name('admin.popup-campaign.edit');
Route::post('/popup-campaign', [PopupCampaignController::class, 'update'])->name('admin.popup-campaign.update');
    Route::resource('branches', BranchController::class)->names('admin.branches');
    Route::resource('categories', CategoryController::class)->names('admin.categories');
    Route::resource('products', ProductController::class)->names('admin.products');
Route::middleware('permission:manage_staff')->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('admin.staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
});Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('admin.ai.index');
Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('admin.ai.ask');
    /*
    |--------------------------------------------------------------------------
    | Digital Menu Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/digital-menu/settings', [DigitalMenuSettingController::class, 'edit'])->name('admin.digital-menu.settings');
    Route::post('/digital-menu/settings', [DigitalMenuSettingController::class, 'update'])->name('admin.digital-menu.settings.update');

    Route::get('/digital-menu/categories', [DigitalMenuCategoryController::class, 'index'])->name('admin.digital-menu.categories');
    Route::post('/digital-menu/categories', [DigitalMenuCategoryController::class, 'store'])->name('admin.digital-menu.categories.store');
    Route::put('/digital-menu/categories/{category}', [DigitalMenuCategoryController::class, 'update'])->name('admin.digital-menu.categories.update');
    Route::delete('/digital-menu/categories/{category}', [DigitalMenuCategoryController::class, 'destroy'])->name('admin.digital-menu.categories.destroy');

    Route::get('/digital-menu/items', [DigitalMenuItemController::class, 'index'])->name('admin.digital-menu.items');
    Route::post('/digital-menu/items', [DigitalMenuItemController::class, 'store'])->name('admin.digital-menu.items.store');
    Route::put('/digital-menu/items/{item}', [DigitalMenuItemController::class, 'update'])->name('admin.digital-menu.items.update');
    Route::delete('/digital-menu/items/{item}', [DigitalMenuItemController::class, 'destroy'])->name('admin.digital-menu.items.destroy');

    Route::get('/digital-menu/qr', [DigitalMenuQrController::class, 'index'])->name('admin.digital-menu.qr');
    Route::get('/digital-menu/qr/image', [DigitalMenuQrController::class, 'image'])->name('admin.digital-menu.qr.image');
    Route::get('/digital-menu/qr/download', [DigitalMenuQrController::class, 'download'])->name('admin.digital-menu.qr.download');
    Route::get('/digital-menu/qr/print', [DigitalMenuQrController::class, 'print'])->name('admin.digital-menu.qr.print');

    /*
    |--------------------------------------------------------------------------
    | Product Options
    |--------------------------------------------------------------------------
    */

    Route::get('/products/{product}/options', [ProductOptionGroupController::class, 'index'])->name('admin.products.options.index');
    Route::post('/products/{product}/options', [ProductOptionGroupController::class, 'store'])->name('admin.products.options.store');
    Route::put('/products/{product}/options/{group}', [ProductOptionGroupController::class, 'update'])->name('admin.products.options.update');
    Route::delete('/products/{product}/options/{group}', [ProductOptionGroupController::class, 'destroy'])->name('admin.products.options.destroy');

    Route::post('/products/{product}/options/{group}/items', [ProductOptionItemController::class, 'store'])->name('admin.products.options.items.store');
    Route::put('/products/{product}/options/{group}/items/{item}', [ProductOptionItemController::class, 'update'])->name('admin.products.options.items.update');
    Route::delete('/products/{product}/options/{group}/items/{item}', [ProductOptionItemController::class, 'destroy'])->name('admin.products.options.items.destroy');

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders-delivery', [OrderController::class, 'deliveryOrders'])->name('admin.orders.delivery');
    Route::get('/orders-pickup', [OrderController::class, 'pickupOrders'])->name('admin.orders.pickup');
    Route::get('/orders/poll', [OrderController::class, 'poll'])->name('admin.orders.poll');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::patch('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('admin.orders.assign-delivery');
    Route::patch('/orders/{order}/assignDelivery', [OrderController::class, 'assignDelivery']);

    Route::get('/delivery-dashboard', [DeliveryDashboardController::class, 'index'])
        ->name('admin.delivery.dashboard');
    Route::get('/delivery', [DeliveryDashboardController::class, 'index']);

    Route::get('/delivery/orders', [DeliveryOrderController::class, 'index'])->name('admin.delivery.orders.index');
    Route::get('/delivery/orders/active', [DeliveryOrderController::class, 'active'])->name('admin.delivery.orders.active');
    Route::get('/delivery/orders/completed', [DeliveryOrderController::class, 'completed'])->name('admin.delivery.orders.completed');
    Route::get('/delivery-management', [DeliveryManagementController::class, 'index'])->name('admin.delivery.management');
});

Route::prefix('delivery')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DeliveryOrderController::class, 'index'])->name('delivery.orders.index');
    Route::get('/active', [DeliveryOrderController::class, 'active'])->name('delivery.orders.active');
    Route::get('/completed', [DeliveryOrderController::class, 'completed'])->name('delivery.orders.completed');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/delivery-dashboard', [DeliveryDashboardController::class, 'index']);
});

require __DIR__ . '/auth.php';
