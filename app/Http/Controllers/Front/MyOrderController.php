<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class MyOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('front.my-orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');

        return view('front.my-orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->canBeCancelledByCustomer()) {
            return redirect()->back()->with('error', 'لا يمكن إلغاء هذا الطلب الآن');
        }

        $order->update([
            'status' => 'cancelled',
            'status_note' => 'تم إلغاء الطلب بواسطة العميل',
        ]);

        return redirect()->back()->with('success', 'تم إلغاء الطلب بنجاح');
    }

    public function reorder(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $cart = [];

        foreach ($order->items as $item) {
            $cart[$item->product_id] = [
                'product_id' => $item->product_id,
                'name'       => $item->product_name,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
                'image'      => null,
                'total'      => $item->total,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'تمت إعادة الطلب إلى السلة');
    }
}