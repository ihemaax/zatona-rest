<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class DeliveryOrderController extends Controller
{
    protected function forDeliveryUser($query)
    {
        $user = auth()->user();

        abort_unless($user && $user->role === User::ROLE_DELIVERY, 403, 'هذه الصفحة مخصصة للدليفري فقط.');

        return $query->where('delivery_user_id', $user->id);
    }

    public function index()
    {
        $orders = $this->forDeliveryUser(Order::with('branch')->latest())->paginate(20);

        return view('delivery.orders.index', compact('orders'));
    }

    public function active()
    {
        $orders = $this->forDeliveryUser(
            Order::with('branch')
                ->whereIn('status', ['confirmed', 'preparing', 'out_for_delivery'])
                ->latest()
        )->paginate(20);

        return view('delivery.orders.active', compact('orders'));
    }

    public function completed()
    {
        $orders = $this->forDeliveryUser(
            Order::with('branch')
                ->where('status', 'delivered')
                ->latest()
        )->paginate(20);

        return view('delivery.orders.completed', compact('orders'));
    }
}

