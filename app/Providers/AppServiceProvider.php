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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->fallbackFromRedisIfExtensionMissing();
        $this->validateCriticalSecrets();

        Paginator::useBootstrapFive();

        Setting::observe(InvalidateFrontCacheObserver::class);
        PopupCampaign::observe(InvalidateFrontCacheObserver::class);
        Product::observe(InvalidateFrontCacheObserver::class);
        Category::observe(InvalidateFrontCacheObserver::class);
        ProductOptionGroup::observe(InvalidateFrontCacheObserver::class);
        ProductOptionItem::observe(InvalidateFrontCacheObserver::class);

        if (config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $sharedSetting = Setting::first();

        View::share('setting', $sharedSetting);

        RateLimiter::for('auth-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(6)->by($request->ip()),
                Limit::perMinute(8)->by($email . '|' . $request->ip()),
                Limit::perHour(40)->by($request->ip() . '|login-hourly'),
            ];
        });

        RateLimiter::for('cart', function (Request $request) {
            return Limit::perMinute(80)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () use ($request) {
                    Log::warning('rate_limit.cart', [
                        'ip' => $request->ip(),
                        'user_id' => $request->user()?->id,
                        'path' => $request->path(),
                    ]);

                    return response()->json([
                        'message' => 'تم تجاوز عدد المحاولات المسموح به على السلة، حاول بعد دقيقة.',
                    ], 429);
                });
        });

        RateLimiter::for('checkout-coupon', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () use ($request) {
                    Log::warning('rate_limit.checkout_coupon', [
                        'ip' => $request->ip(),
                        'user_id' => $request->user()?->id,
                    ]);

                    return redirect()->back()->with('error', 'محاولات التحقق من الكوبون كثيرة جدًا، حاول بعد دقيقة.');
                });
        });

        RateLimiter::for('checkout-store', function (Request $request) {
            return [
                Limit::perMinute(8)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(30)->by(($request->user()?->id ?: $request->ip()) . '|hourly'),
            ];
        });

        RateLimiter::for('admin-ai', function (Request $request) {
            return [
                Limit::perMinute(20)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(200)->by(($request->user()?->id ?: $request->ip()) . '|admin-ai-hourly'),
            ];
        });

        RateLimiter::for('admin-actions', function (Request $request) {
            $actorKey = (string) ($request->user()?->id ?: $request->ip());

            return [
                Limit::perMinute(60)->by($actorKey),
                Limit::perMinute(20)->by($actorKey . '|' . $request->path()),
                Limit::perHour(500)->by($actorKey . '|admin-hourly'),
            ];
        });

        RateLimiter::for('checkout-otp-send', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip()),
                Limit::perHour(10)->by($request->ip() . '|checkout-otp-send-hourly'),
            ];
        });

        RateLimiter::for('checkout-otp-verify', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perHour(60)->by($request->ip() . '|checkout-otp-verify-hourly'),
            ];
        });



        View::composer('layouts.admin', function ($view) {
            $adminUser = auth()->user();
            $newOrdersCount = 0;

            if ($adminUser) {
                $query = Order::where('is_seen_by_admin', false);

                if (!$adminUser->isSuperAdmin() && !$adminUser->hasPermission('view_all_branches_orders') && $adminUser->branch_id) {
                    $query->where(function ($branchScopedQuery) use ($adminUser) {
                        $branchScopedQuery
                            ->where('branch_id', $adminUser->branch_id)
                            ->orWhereNull('branch_id');
                    });
                }

                $newOrdersCount = $query->count();
            }

            $view->with('layoutAdminNewOrdersCount', $newOrdersCount);
        });

        View::composer('layouts.app', function ($view) {
            $newOrdersCount = 0;
            if (auth()->check() && auth()->user()->canAccessAdminPanel()) {
                $newOrdersCount = Order::where('is_seen_by_admin', false)->count();
            }

            $view->with('layoutNewOrdersCount', $newOrdersCount);
        });
    }

    protected function validateCriticalSecrets(): void
    {
        if (!app()->isProduction()) {
            return;
        }

        $missing = [];

        if ((string) config('app.key') === '') {
            $missing[] = 'APP_KEY';
        }

        $wpsEnabled = (bool) config('services.wapilot.enabled', true);
        $wpsApiKey = (string) config('services.wapilot.api_token');
        if ($wpsEnabled && $wpsApiKey === '') {
            $missing[] = 'WAPILOT_API_TOKEN';
        }

        if ($missing !== []) {
            Log::critical('critical.secrets.missing', [
                'missing' => $missing,
            ]);
        }
    }

    protected function fallbackFromRedisIfExtensionMissing(): void
    {
        $redisClient = (string) config('database.redis.client', 'phpredis');
        $redisExtensionMissing = $redisClient === 'phpredis' && !class_exists(\Redis::class);

        if (!$redisExtensionMissing) {
            return;
        }

        if ((string) config('cache.default') === 'redis') {
            config(['cache.default' => 'database']);
        }

        if ((string) config('session.driver') === 'redis') {
            config(['session.driver' => 'database']);
            config(['session.store' => null]);
        }

        if ((string) config('queue.default') === 'redis') {
            config(['queue.default' => 'database']);
        }

        Log::warning('redis.extension.missing_fallback_applied', [
            'cache' => config('cache.default'),
            'session' => config('session.driver'),
            'queue' => config('queue.default'),
        ]);
    }
}
