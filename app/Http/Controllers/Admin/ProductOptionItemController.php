<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionItem;
use Illuminate\Http\Request;

class ProductOptionItemController extends Controller
{
    public function store(Request $request, Product $product, ProductOptionGroup $group)
    {
        if ($group->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_default' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        if ($validated['is_default']) {
            $group->items()->update(['is_default' => false]);
        }

        $group->items()->create([
            'name' => $validated['name'],
            'price' => $validated['price'] ?? 0,
            'is_default' => $validated['is_default'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'تمت إضافة العنصر بنجاح');
    }

    public function update(Request $request, Product $product, ProductOptionGroup $group, ProductOptionItem $item)
    {
        if ($group->product_id !== $product->id || $item->product_option_group_id !== $group->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_default' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        if ($validated['is_default']) {
            $group->items()->update(['is_default' => false]);
        }

        $item->update([
            'name' => $validated['name'],
            'price' => $validated['price'] ?? 0,
            'is_default' => $validated['is_default'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'تم تعديل العنصر بنجاح');
    }

    public function destroy(Product $product, ProductOptionGroup $group, ProductOptionItem $item)
    {
        if ($group->product_id !== $product->id || $item->product_option_group_id !== $group->id) {
            abort(404);
        }

        $item->delete();

        return redirect()->back()->with('success', 'تم حذف العنصر بنجاح');
    }
}