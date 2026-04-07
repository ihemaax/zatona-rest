<?php

use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerLeadController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryDashboardController;
use App\Http\Controllers\Admin\DeliveryManagementController;
use App\Http\Controllers\Admin\DigitalMenuCategoryController;
use App\Http\Controllers\Admin\DigitalMenuItemController;
use App\Http\Controllers\Admin\DigitalMenuQrController;
use App\Http\Controllers\Admin\DigitalMenuSettingController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PopupCampaignController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductOptionGroupController;
use App\Http\Controllers\Admin\ProductOptionItemController;
use App\Http\Controllers\Admin\ReadyOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Demo\AdminDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/demo/admin-dashboard', [DashboardController::class, 'demo'])->name('admin.dashboard.demo');
Route::get('/demo/admin/{path?}', [AdminDemoController::class, 'show'])
    ->where('path', '.*')
    ->name('admin.demo.module');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/poll', [DashboardController::class, 'poll'])->name('admin.dashboard.poll');
    Route::get('/dashboard/export-snapshot', [DashboardController::class, 'exportSnapshot'])->name('admin.dashboard.export-snapshot');

    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
        Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/popup-campaign', [PopupCampaignController::class, 'edit'])->name('admin.popup-campaign.edit');
        Route::post('/popup-campaign', [PopupCampaignController::class, 'update'])->name('admin.popup-campaign.update');
    });

    Route::middleware('permission:manage_branches')->group(function () {
        Route::resource('branches', BranchController::class)->names('admin.branches');
    });

    Route::middleware('permission:manage_categories')->group(function () {
        Route::resource('categories', CategoryController::class)->names('admin.categories');
    });

    Route::middleware('permission:manage_products')->group(function () {
        Route::resource('products', ProductController::class)->names('admin.products');

        /* Product Options */
        Route::get('/products/{product}/options', [ProductOptionGroupController::class, 'index'])->name('admin.products.options.index');
        Route::post('/products/{product}/options', [ProductOptionGroupController::class, 'store'])->name('admin.products.options.store');
        Route::put('/products/{product}/options/{group}', [ProductOptionGroupController::class, 'update'])->name('admin.products.options.update');
        Route::delete('/products/{product}/options/{group}', [ProductOptionGroupController::class, 'destroy'])->name('admin.products.options.destroy');

        Route::post('/products/{product}/options/{group}/items', [ProductOptionItemController::class, 'store'])->name('admin.products.options.items.store');
        Route::put('/products/{product}/options/{group}/items/{item}', [ProductOptionItemController::class, 'update'])->name('admin.products.options.items.update');
        Route::delete('/products/{product}/options/{group}/items/{item}', [ProductOptionItemController::class, 'destroy'])->name('admin.products.options.items.destroy');
    });

    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
        Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');
    });

    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('admin.reports.export.excel');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.export.pdf');
    });

    Route::middleware('permission:view_audit_logs')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
    });

    Route::middleware('permission:view_customer_leads')->group(function () {
        Route::get('/customer-leads', [CustomerLeadController::class, 'index'])->name('admin.customer-leads.index');
        Route::get('/customer-leads/export/excel', [CustomerLeadController::class, 'export'])->name('admin.customer-leads.export.excel');
    });

    Route::middleware('permission:manage_staff')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
    });

    Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('admin.ai.index');
    Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])
        ->middleware('throttle:admin-ai')
        ->name('admin.ai.ask');

    /* Digital Menu */
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


    /* Cashier */
    Route::middleware('permission:use_cashier')->group(function () {
        Route::get('/cashier/pos/{branch}', [CashierController::class, 'pos'])->name('admin.cashier.pos');
        Route::post('/cashier/pos/{branch}/checkout', [CashierController::class, 'checkout'])->name('admin.cashier.checkout');
        Route::get('/cashier/pos/{branch}/invoice/{order}', [CashierController::class, 'invoice'])->name('admin.cashier.invoice');
    });

    Route::middleware('permission:manage_cashier')->group(function () {
        Route::get('/cashier', [CashierController::class, 'index'])->name('admin.cashier.index');
        Route::post('/cashier/menu-items', [CashierController::class, 'storeMenuItem'])->name('admin.cashier.menu-items.store');
        Route::put('/cashier/menu-items/{item}', [CashierController::class, 'updateMenuItem'])->name('admin.cashier.menu-items.update');
        Route::delete('/cashier/menu-items/{item}', [CashierController::class, 'destroyMenuItem'])->name('admin.cashier.menu-items.destroy');
    });

    /* Orders */
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders-delivery', [OrderController::class, 'deliveryOrders'])->name('admin.orders.delivery');
    Route::get('/orders-pickup', [OrderController::class, 'pickupOrders'])->name('admin.orders.pickup');
    Route::get('/orders/poll', [OrderController::class, 'poll'])->name('admin.orders.poll');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::patch('/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])->name('admin.orders.assign-delivery');

    Route::get('/kitchen', [KitchenController::class, 'index'])->name('admin.kitchen.index');
    Route::get('/kitchen/poll', [KitchenController::class, 'poll'])->name('admin.kitchen.poll');
    Route::post('/kitchen/{order}/start', [KitchenController::class, 'start'])->name('admin.kitchen.start');
    Route::post('/kitchen/{order}/ready', [KitchenController::class, 'ready'])->name('admin.kitchen.ready');

    Route::get('/ready-orders', [ReadyOrderController::class, 'index'])->name('admin.orders.ready');
    Route::get('/ready-orders/poll', [ReadyOrderController::class, 'poll'])->name('admin.orders.ready.poll');

    Route::get('/delivery-dashboard', [DeliveryDashboardController::class, 'index'])->name('admin.delivery.dashboard');
    Route::get('/delivery-management', [DeliveryManagementController::class, 'index'])
        ->middleware('permission:manage_delivery')
        ->name('admin.delivery.management');

    Route::redirect('/delivery', '/admin/delivery-dashboard');
    Route::redirect('/delivery/orders', '/admin/delivery-dashboard');
    Route::redirect('/delivery/orders/active', '/admin/delivery-dashboard');
    Route::redirect('/delivery/orders/completed', '/admin/delivery-dashboard');
});

Route::redirect('/delivery', '/admin/delivery-dashboard');
Route::redirect('/delivery/active', '/admin/delivery-dashboard');
Route::redirect('/delivery/completed', '/admin/delivery-dashboard');
Route::redirect('/delivery-dashboard', '/admin/delivery-dashboard');
