<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashierMenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    protected function canAccessBranch(?User $user, Branch $branch): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->canViewAllBranchesOrders()) {
            return true;
        }

        if (!$user->branch_id) {
            return false;
        }

        return (int) $user->branch_id === (int) $branch->id;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedBranchId = (int) $request->get('branch_id', $user->branch_id);

        $branchesQuery = Branch::query()->where('is_active', true)->orderBy('name');
        if (!$user->canViewAllBranchesOrders()) {
            $branchesQuery->where('id', $user->branch_id);
        }

        $branches = $branchesQuery->get();
        $selectedBranch = $branches->firstWhere('id', $selectedBranchId) ?? $branches->first();

        $menuItems = collect();
        if ($selectedBranch) {
            $menuItems = CashierMenuItem::with('product.category')
                ->where('branch_id', $selectedBranch->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return view('admin.cashier.index', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'products' => Product::with('category')->where('is_available', true)->orderBy('name')->get(),
            'menuItems' => $menuItems,
        ]);
    }

    public function storeMenuItem(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);
        abort_unless($this->canAccessBranch(auth()->user(), $branch), 403, 'لا يمكنك تعديل منيو فرع آخر.');

        CashierMenuItem::updateOrCreate(
            [
                'branch_id' => $validated['branch_id'],
                'product_id' => $validated['product_id'],
            ],
            [
                'price' => $validated['price'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return back()->with('success', 'تم حفظ المنتج في منيو الكاشير بنجاح.');
    }

    public function updateMenuItem(Request $request, CashierMenuItem $item)
    {
        abort_unless($this->canAccessBranch(auth()->user(), $item->branch), 403, 'لا يمكنك تعديل منيو فرع آخر.');

        $validated = $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'price' => $validated['price'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم تحديث منتج الكاشير.');
    }

    public function destroyMenuItem(CashierMenuItem $item)
    {
        abort_unless($this->canAccessBranch(auth()->user(), $item->branch), 403, 'لا يمكنك تعديل منيو فرع آخر.');
        $item->delete();

        return back()->with('success', 'تم حذف المنتج من منيو الكاشير.');
    }

    public function pos(Branch $branch)
    {
        abort_unless($this->canAccessBranch(auth()->user(), $branch), 403, 'لا يمكنك استخدام كاشير فرع آخر.');

        $menuItems = CashierMenuItem::with('product.category')
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.cashier.pos', [
            'branch' => $branch,
            'menuItems' => $menuItems,
        ]);
    }

    public function checkout(Request $request, Branch $branch)
    {
        abort_unless($this->canAccessBranch(auth()->user(), $branch), 403, 'لا يمكنك استخدام كاشير فرع آخر.');

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:cashier_menu_items,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $menuItems = CashierMenuItem::with('product')
            ->where('branch_id', $branch->id)
            ->whereIn('id', collect($validated['items'])->pluck('id')->all())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        abort_if($menuItems->isEmpty(), 422, 'قائمة الأصناف غير متاحة.');

        $subtotal = 0;
        $itemRows = [];

        foreach ($validated['items'] as $line) {
            $menuItem = $menuItems->get($line['id']);
            if (!$menuItem) {
                continue;
            }

            $price = (float) ($menuItem->price ?? $menuItem->product->price);
            $qty = (int) $line['qty'];
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $itemRows[] = [
                'product_id' => $menuItem->product_id,
                'product_name' => $menuItem->product->name,
                'price' => $price,
                'quantity' => $qty,
                'total' => $lineTotal,
            ];
        }

        abort_if(empty($itemRows), 422, 'لا يوجد أصناف صالحة لإنشاء الفاتورة.');

        $order = DB::transaction(function () use ($branch, $validated, $subtotal, $itemRows) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_type' => 'pickup',
                'branch_id' => $branch->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? 'غير متوفر',
                'address_line' => 'داخل المطعم - طلب كاشير',
                'area' => $branch->name,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'delivery_fee' => 0,
                'total' => $subtotal,
                'payment_method' => 'cash',
                'status' => 'confirmed',
                'estimated_delivery_minutes' => 15,
                'estimated_delivery_at' => now()->addMinutes(15),
                'is_seen_by_admin' => true,
            ]);

            foreach ($itemRows as $row) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'total' => $row['total'],
                    'selected_options' => [],
                ]);
            }

            return $order->fresh('items');
        });

        return redirect()->route('admin.cashier.invoice', [$branch, $order]);
    }

    public function invoice(Branch $branch, Order $order)
    {
        abort_unless($this->canAccessBranch(auth()->user(), $branch), 403, 'لا يمكنك عرض فاتورة فرع آخر.');
        abort_if((int) $order->branch_id !== (int) $branch->id, 404);

        $order->load('items');

        return view('admin.cashier.invoice', [
            'branch' => $branch,
            'order' => $order,
        ]);
    }
}
