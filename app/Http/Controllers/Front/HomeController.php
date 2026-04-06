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
        $setting = Cache::remember('front.home.setting', now()->addMinutes(5), function () {
            return Setting::first();
        });

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

        $popupCampaign = PopupCampaign::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();

        return view('front.home', compact('products', 'setting', 'popupCampaign'));
    }
}
