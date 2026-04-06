<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0.01',
            'min_order_total' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        if (($data['type'] ?? null) === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'نسبة الخصم يجب أن تكون من 0 إلى 100'])->withInput();
        }

        Coupon::create([
            'code' => Str::upper(trim((string) $data['code'])),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_total' => $data['min_order_total'] ?? 0,
            'max_discount' => $data['max_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'تم إنشاء كوبون الخصم بنجاح');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0.01',
            'min_order_total' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'required|boolean',
        ]);

        if (($data['type'] ?? null) === 'percent' && (float) $data['value'] > 100) {
            return back()->withErrors(['value' => 'نسبة الخصم يجب أن تكون من 0 إلى 100'])->withInput();
        }

        $coupon->update([
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_total' => $data['min_order_total'] ?? 0,
            'max_discount' => $data['max_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        return back()->with('success', 'تم تحديث الكوبون بنجاح');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('success', 'تم حذف الكوبون');
    }
}
