<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class PageController extends Controller
{
    public function about()
    {
        $setting = Setting::first();
        return view('front.pages.about', compact('setting'));
    }

    public function contact()
    {
        $setting = Setting::first();
        return view('front.pages.contact', compact('setting'));
    }

    public function privacy()
    {
        $setting = Setting::first();
        return view('front.pages.privacy', compact('setting'));
    }

    public function faq()
    {
        $setting = Setting::first();
        return view('front.pages.faq', compact('setting'));
    }
}