<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PopupCampaign;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

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
                'button_text' => $popup->button_text,
                'button_url' => $popup->button_url,
                'show_once' => (bool) $popup->show_once,
                'starts_at' => optional($popup->starts_at)->toDateTimeString(),
                'ends_at' => optional($popup->ends_at)->toDateTimeString(),
            ];
        });

        $setting = $settingPayload ? new Setting($settingPayload) : null;
        $popupCampaign = $popupPayload ? new PopupCampaign($popupPayload) : null;

        $products = Cache::remember('front.home.products.v3', now()->addMinutes(5), function () {
            return Product::with([
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
        });

        return view('front.home', compact('products', 'setting', 'popupCampaign'));
    }
}
