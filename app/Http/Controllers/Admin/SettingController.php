<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([
                'restaurant_name' => 'Play Casy',
                'restaurant_phone' => '',
                'restaurant_address' => '',
                'delivery_fee' => 0,
                'is_open' => true,
            ]);
        }

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_phone' => 'nullable|string|max:50',
            'restaurant_address' => 'nullable|string|max:255',
            'delivery_fee' => 'required|numeric|min:0',
            'is_open' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $setting = Setting::first();

        if (!$setting) {
            $setting = Setting::create([
                'restaurant_name' => 'Play Casy',
                'restaurant_phone' => '',
                'restaurant_address' => '',
                'delivery_fee' => 0,
                'is_open' => true,
            ]);
        }

        $data = [
            'restaurant_name' => $validated['restaurant_name'],
            'restaurant_phone' => $validated['restaurant_phone'] ?? null,
            'restaurant_address' => $validated['restaurant_address'] ?? null,
            'delivery_fee' => $validated['delivery_fee'],
            'is_open' => $validated['is_open'],
        ];

        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($setting->banner && Storage::disk('public')->exists($setting->banner)) {
                Storage::disk('public')->delete($setting->banner);
            }

            $data['banner'] = $request->file('banner')->store('settings', 'public');
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'تم تحديث إعدادات المطعم بنجاح');
    }
}