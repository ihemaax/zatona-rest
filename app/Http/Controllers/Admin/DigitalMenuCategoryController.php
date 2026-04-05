<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DigitalMenuCategoryController extends Controller
{
    public function index()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            ['title' => 'Digital Menu', 'slug' => 'main-menu']
        );

        $categories = DigitalMenuCategory::where('digital_menu_setting_id', $setting->id)
            ->withCount('items')
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view('admin.digital-menu.categories', compact('setting', 'categories'));
    }

    public function store(Request $request)
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            ['title' => 'Digital Menu', 'slug' => 'main-menu']
        );

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        DigitalMenuCategory::create([
            'digital_menu_setting_id' => $setting->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم إضافة القسم بنجاح.');
    }

    public function update(Request $request, DigitalMenuCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(DigitalMenuCategory $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'تم حذف القسم بنجاح.');
    }
}