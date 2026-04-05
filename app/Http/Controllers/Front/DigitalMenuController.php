<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenuSetting;

class DigitalMenuController extends Controller
{
    public function show(string $slug)
    {
        $setting = DigitalMenuSetting::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categories = $setting->categories()
            ->where('is_active', true)
            ->with(['items' => function ($query) {
                $query->where('is_available', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('front.digital-menu.show', compact('setting', 'categories'));
    }
}