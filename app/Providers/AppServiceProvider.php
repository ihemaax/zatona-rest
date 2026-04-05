<?php

namespace App\Providers;

use App\Models\Setting;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DeliveryDashboardController;
use App\Http\Controllers\Admin\DeliveryOrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::share('setting', Setting::first());

        // Fallback registration in case route cache is stale in production/local.
        if (!Route::has('admin.orders.assign-delivery')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->patch('/admin/orders/{order}/assign-delivery', [OrderController::class, 'assignDelivery'])
                ->name('admin.orders.assign-delivery');
        }

        if (!Route::has('admin.delivery.dashboard')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->get('/admin/delivery-dashboard', [DeliveryDashboardController::class, 'index'])
                ->name('admin.delivery.dashboard');
        }

        if (!Route::has('delivery.orders.index')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->get('/delivery', [DeliveryOrderController::class, 'index'])
                ->name('delivery.orders.index');
        }
    }
}
