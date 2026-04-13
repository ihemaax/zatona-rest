<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        foreach ($cart as $key => $item) {
            if (!isset($item['cart_key'])) {
                $cart[$key]['cart_key'] = $key;
            }
        }

        session()->put('cart', $cart);

        return view('front.cart', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $product->load([
            'optionGroups' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
            'optionGroups.items' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
        ]);

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $quantity = (int) $request->quantity;
        $basePrice = (float) $product->price;
        $selectedOptions = [];
        $extraPrice = 0;

        foreach ($product->optionGroups as $group) {
            // يدعم الطريقتين:
            // 1) القديمة: group_1 / group_2
            // 2) الجديدة من المودال: options[1] / options[2][]
            $legacyInputName = 'group_' . $group->id;
            $submitted = $request->input($legacyInputName);

            if ($submitted === null) {
                $submitted = $request->input('options.' . $group->id);
            }

            if ($group->type === 'single') {
                if ($group->is_required && empty($submitted)) {
                    return $this->cartErrorResponse(
                        $request,
                        'يرجى اختيار: ' . $group->name
                    );
                }

                if (!empty($submitted)) {
                    $item = $group->items->firstWhere('id', (int) $submitted);

                    if ($item) {
                        $selectedOptions[] = [
                            'group_name' => $group->name,
                            'item_name' => $item->name,
                            'price' => (float) $item->price,
                        ];

                        $extraPrice += (float) $item->price;
                    }
                }
            } else {
                $submitted = is_array($submitted) ? $submitted : [];

                if ($group->is_required && count($submitted) === 0) {
                    return $this->cartErrorResponse(
                        $request,
                        'يرجى اختيار عنصر واحد على الأقل من: ' . $group->name
                    );
                }

                if (!empty($group->max_selection) && count($submitted) > (int) $group->max_selection) {
                    return $this->cartErrorResponse(
                        $request,
                        'الحد الأقصى لمجموعة ' . $group->name . ' هو ' . $group->max_selection
                    );
                }

                foreach ($submitted as $itemId) {
                    $item = $group->items->firstWhere('id', (int) $itemId);

                    if ($item) {
                        $selectedOptions[] = [
                            'group_name' => $group->name,
                            'item_name' => $item->name,
                            'price' => (float) $item->price,
                        ];

                        $extraPrice += (float) $item->price;
                    }
                }
            }
        }

        $unitPrice = $basePrice + $extraPrice;
        $lineTotal = $unitPrice * $quantity;
        $notes = trim((string) $request->input('notes', ''));

        $cart = session()->get('cart', []);
        $cartKey = uniqid('cart_', true);

        $cart[$cartKey] = [
            'cart_key' => $cartKey,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $unitPrice,
            'base_price' => $basePrice,
            'extra_price' => $extraPrice,
            'quantity' => $quantity,
            'image' => $product->image,
            'selected_options' => $selectedOptions,
            'notes' => $notes !== '' ? $notes : null,
            'total' => $lineTotal,
        ];

        session()->put('cart', $cart);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المنتج إلى السلة',
                'cart_count' => count($cart),
                'cart_total' => $this->calculateCartTotal($cart),
                'item' => [
                    'cart_key' => $cartKey,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'total' => $lineTotal,
                ],
            ]);
        }

        return redirect()->back()
            ->with('success', 'تمت إضافة المنتج إلى السلة')
            ->with('item_added_to_cart', true);
    }

    public function update(Request $request, string $cartKey)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = (int) $request->quantity;
            $cart[$cartKey]['total'] = $cart[$cartKey]['price'] * $cart[$cartKey]['quantity'];
            session()->put('cart', $cart);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث السلة',
                'cart_count' => count($cart),
                'cart_total' => $this->calculateCartTotal($cart),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'تم تحديث السلة');
    }

    public function remove(Request $request, string $cartKey)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('cart', $cart);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المنتج من السلة',
                'cart_count' => count($cart),
                'cart_total' => $this->calculateCartTotal($cart),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'تم حذف المنتج من السلة');
    }

    protected function calculateCartTotal(array $cart): float
    {
        return (float) collect($cart)->sum(function ($item) {
            return (float) ($item['total'] ?? 0);
        });
    }

    protected function cartErrorResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with('error', $message);
    }
}
