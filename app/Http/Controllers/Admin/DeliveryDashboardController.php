<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DeliveryDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_unless($user && $user->role === 'delivery', 403, 'هذه الصفحة مخصصة للدليفري فقط.');

        $orders = Order::with('branch')
            ->where('delivery_user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('admin.delivery.dashboard', [
            'orders' => $orders,
        ]);
    }
}

