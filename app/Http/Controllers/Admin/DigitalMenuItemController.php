<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuItem;
use Illuminate\Http\Request;

class DigitalMenuItemController extends Controller
{
    public function index()
    {
        $categories = DigitalMenuCategory::where('is_active', true)
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')->latest('id')])
            ->orderBy('sort_order')
            ->get();

        return view('admin.digital-menu.items', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'digital_menu_category_id' => 'required|exists:digital_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'badge' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:4096',
            'is_available' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('digital-menu', 'public');
        }

        $validated['is_available'] = $request->boolean('is_available');
        $validated['price'] = $validated['price'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        DigitalMenuItem::create($validated);

        return redirect()->back()->with('success', 'تم إضافة المنتج للمنيو الإلكتروني.');
    }

    public function update(Request $request, DigitalMenuItem $item)
    {
        $validated = $request->validate([
            'digital_menu_category_id' => 'required|exists:digital_menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'badge' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|max:4096',
            'is_available' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('digital-menu', 'public');
        }

        $validated['is_available'] = $request->boolean('is_available');
        $validated['price'] = $validated['price'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $item->update($validated);

        return redirect()->back()->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy(DigitalMenuItem $item)
    {
        $item->delete();

        return redirect()->back()->with('success', 'تم حذف المنتج من المنيو الإلكتروني.');
    }
}