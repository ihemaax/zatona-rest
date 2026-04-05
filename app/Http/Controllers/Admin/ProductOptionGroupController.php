<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductOptionGroupController extends Controller
{
    public function index(Product $product)
    {
        $product->load([
            'optionGroups.items' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        $groups = $product->optionGroups()->orderBy('sort_order')->get();

        return view('admin.products.options.index', compact('product', 'groups'));
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'required|boolean',
            'max_selection' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        ProductOptionGroup::create([
            'product_id' => $product->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'is_required' => (bool) $validated['is_required'],
            'max_selection' => $validated['type'] === 'multiple'
                ? ($validated['max_selection'] ?? null)
                : 1,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'تم إضافة مجموعة الخيارات بنجاح.');
    }

    public function update(Request $request, Product $product, ProductOptionGroup $group)
    {
        if ($group->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'required|boolean',
            'max_selection' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $group->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'is_required' => (bool) $validated['is_required'],
            'max_selection' => $validated['type'] === 'multiple'
                ? ($validated['max_selection'] ?? null)
                : 1,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'تم تحديث مجموعة الخيارات بنجاح.');
    }

    public function destroy(Product $product, ProductOptionGroup $group)
    {
        if ($group->product_id !== $product->id) {
            abort(404);
        }

        $group->delete();

        return redirect()->back()->with('success', 'تم حذف مجموعة الخيارات بنجاح.');
    }
}