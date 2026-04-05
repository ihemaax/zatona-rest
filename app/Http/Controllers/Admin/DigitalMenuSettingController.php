<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenuSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DigitalMenuSettingController extends Controller
{
    public function edit()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Digital Menu',
                'slug' => 'main-menu',
                'is_active' => true,
                'show_prices' => true,
                'show_descriptions' => true,
            ]
        );

        return view('admin.digital-menu.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = DigitalMenuSetting::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:digital_menu_settings,slug,' . $setting->id,
            'subtitle' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'show_prices' => 'nullable|boolean',
            'show_descriptions' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'logo' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('digital-menu', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('digital-menu', 'public');
        }

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['show_prices'] = $request->boolean('show_prices');
        $validated['show_descriptions'] = $request->boolean('show_descriptions');
        $validated['is_active'] = $request->boolean('is_active');

        $setting->update($validated);

        return redirect()->back()->with('success', 'تم تحديث إعدادات المنيو الإلكتروني بنجاح.');
    }
}