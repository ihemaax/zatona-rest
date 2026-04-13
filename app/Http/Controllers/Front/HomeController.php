<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\PopupCampaign;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index()
    {
        $settingPayload = Cache::remember('front.home.setting.v3', now()->addMinutes(10), function () {
            $setting = Setting::first();

            if (!$setting) {
                return null;
            }

            return [
                'restaurant_name' => $setting->restaurant_name,
                'restaurant_phone' => $setting->restaurant_phone,
                'restaurant_address' => $setting->restaurant_address,
                'logo' => $setting->logo,
                'banner' => $setting->banner,
                'cover_image' => $setting->cover_image,
                'delivery_fee' => $setting->delivery_fee,
                'is_open' => (bool) $setting->is_open,
            ];
        });

        $popupPayload = Cache::remember('front.home.popup.v2', now()->addMinutes(3), function () {
            $popup = PopupCampaign::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->latest()
                ->first();

            if (!$popup) {
                return null;
            }

            return [
                'id' => $popup->id,
                'title' => $popup->title,
                'subtitle' => $popup->subtitle,
                'description' => $popup->description,
                'image' => $popup->image,
                'show_once' => (bool) $popup->show_once,
                'starts_at' => optional($popup->starts_at)->toDateTimeString(),
                'ends_at' => optional($popup->ends_at)->toDateTimeString(),
            ];
        });

        $setting = $settingPayload ? new Setting($settingPayload) : null;
        $popupCampaign = $popupPayload ? new PopupCampaign($popupPayload) : null;

        $offersPayload = Cache::remember('front.home.offers.v1', now()->addMinutes(3), function () {
            return Offer::query()
                ->activeNow()
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->orderByDesc('id')
                ->get([
                    'id',
                    'name',
                    'short_description',
                    'image',
                    'old_price',
                    'new_price',
                    'sort_order',
                    'starts_at',
                    'ends_at',
                    'is_active',
                ])
                ->map(function (Offer $offer) {
                    return [
                        'id' => $offer->id,
                        'name' => $offer->name,
                        'short_description' => $offer->short_description,
                        'image' => $offer->image,
                        'old_price' => $offer->old_price,
                        'new_price' => $offer->new_price,
                        'sort_order' => $offer->sort_order,
                        'starts_at' => optional($offer->starts_at)->toDateTimeString(),
                        'ends_at' => optional($offer->ends_at)->toDateTimeString(),
                        'is_active' => (bool) $offer->is_active,
                    ];
                })
                ->all();
        });

        $offers = collect($offersPayload)->map(fn (array $payload) => new Offer($payload));

        $products = Product::with([
            'category',
            'optionGroups' => function ($query) {
                $query->orderBy('sort_order')->with([
                    'items' => function ($q) {
                        $q->where('is_active', 1)->orderBy('sort_order');
                    },
                ]);
            },
        ])
            ->where('is_available', 1)
            ->orderBy('id', 'desc')
            ->get();

        return view('front.home', compact('products', 'setting', 'popupCampaign', 'offers'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'category',
            'optionGroups' => function ($query) {
                $query->orderBy('sort_order')->with([
                    'items' => function ($q) {
                        $q->where('is_active', 1)->orderBy('sort_order');
                    },
                ]);
            },
        ]);

        abort_if(!$product->is_available, 404);

        return view('front.product-show', compact('product'));
    }
}
