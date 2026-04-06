<?php

namespace App\Providers;

use App\Observers\InvalidateFrontCacheObserver;
use App\Models\ProductOptionItem;
use App\Models\ProductOptionGroup;
use App\Models\Product;
use App\Models\PopupCampaign;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Order;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DeliveryDashboardController;
use App\Http\Controllers\Admin\DeliveryOrderController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Front\CheckoutController;
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

        Setting::observe(InvalidateFrontCacheObserver::class);
        PopupCampaign::observe(InvalidateFrontCacheObserver::class);
        Product::observe(InvalidateFrontCacheObserver::class);
        Category::observe(InvalidateFrontCacheObserver::class);
        ProductOptionGroup::observe(InvalidateFrontCacheObserver::class);
        ProductOptionItem::observe(InvalidateFrontCacheObserver::class);

        if (config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::share('setting', Setting::first());



        View::composer('layouts.admin', function ($view) {
            $adminUser = auth()->user();
            $newOrdersCount = 0;

            if ($adminUser) {
                $query = Order::where('is_seen_by_admin', false);

                if (!$adminUser->isSuperAdmin() && !$adminUser->hasPermission('view_all_branches_orders') && $adminUser->branch_id) {
                    $query->where('branch_id', $adminUser->branch_id);
                }

                $newOrdersCount = $query->count();
            }

            $view->with('layoutAdminNewOrdersCount', $newOrdersCount);
        });

        View::composer('layouts.app', function ($view) {
            $newOrdersCount = 0;
            if (auth()->check() && auth()->user()->is_admin) {
                $newOrdersCount = Order::where('is_seen_by_admin', false)->count();
            }

            $view->with('layoutNewOrdersCount', $newOrdersCount);
        });


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

        $hasRouteByMethodAndUri = function (string $method, string $uri): bool {
            return collect(Route::getRoutes()->getRoutes())->contains(function ($route) use ($method, $uri) {
                return in_array(strtoupper($method), $route->methods(), true)
                    && $route->uri() === ltrim($uri, '/');
            });
        };

        if (!Route::has('admin.coupons.index') && !$hasRouteByMethodAndUri('GET', '/admin/coupons')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->get('/admin/coupons', [CouponController::class, 'index'])
                ->name('admin.coupons.index');
        }

        if (!Route::has('admin.coupons.store') && !$hasRouteByMethodAndUri('POST', '/admin/coupons')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->post('/admin/coupons', [CouponController::class, 'store'])
                ->name('admin.coupons.store');
        }

        if (!Route::has('admin.coupons.update') && !$hasRouteByMethodAndUri('PUT', '/admin/coupons/{coupon}')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->put('/admin/coupons/{coupon}', [CouponController::class, 'update'])
                ->name('admin.coupons.update');
        }

        if (!Route::has('admin.coupons.destroy') && !$hasRouteByMethodAndUri('DELETE', '/admin/coupons/{coupon}')) {
            Route::middleware(['web', 'auth', 'admin'])
                ->delete('/admin/coupons/{coupon}', [CouponController::class, 'destroy'])
                ->name('admin.coupons.destroy');
        }

        if (!Route::has('checkout.apply-coupon') && !$hasRouteByMethodAndUri('POST', '/checkout/apply-coupon')) {
            Route::middleware('web')
                ->post('/checkout/apply-coupon', [CheckoutController::class, 'applyCoupon'])
                ->name('checkout.apply-coupon');
        }
    }
}
