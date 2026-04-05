<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_order_to_delivery_user(): void
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'address' => 'Main St',
            'is_active' => true,
        ]);

        $admin = $this->makeStaffUser('manager', ['view_orders', 'update_order_status', 'manage_delivery']);
        $delivery = $this->makeStaffUser('delivery', ['view_orders', 'update_order_status']);

        $order = $this->makeOrder($branch->id);

        $this->actingAs($admin)
            ->patch(route('admin.orders.assign-delivery', $order->id), [
                'delivery_user_id' => $delivery->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'delivery_user_id' => $delivery->id,
            'order_type' => 'delivery',
        ]);

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_delivery_user_can_open_assigned_order_and_cannot_open_unassigned_one(): void
    {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'address' => 'Main St',
            'is_active' => true,
        ]);

        $delivery = $this->makeStaffUser('delivery', ['view_orders', 'update_order_status']);
        $otherDelivery = $this->makeStaffUser('delivery', ['view_orders', 'update_order_status']);

        $assignedOrder = $this->makeOrder($branch->id, $delivery->id);
        $otherOrder = $this->makeOrder($branch->id, $otherDelivery->id);

        $this->actingAs($delivery)
            ->get(route('admin.orders.show', $assignedOrder->id))
            ->assertOk();

        $this->actingAs($delivery)
            ->get(route('admin.orders.show', $otherOrder->id))
            ->assertForbidden();
    }

    public function test_delivery_management_requires_manage_delivery_permission(): void
    {
        $managerWithPermission = $this->makeStaffUser('manager', ['view_orders', 'manage_delivery']);
        $managerWithoutPermission = $this->makeStaffUser('manager', ['view_orders']);

        $this->actingAs($managerWithPermission)
            ->get(route('admin.delivery.management'))
            ->assertOk();

        $this->actingAs($managerWithoutPermission)
            ->get(route('admin.delivery.management'))
            ->assertForbidden();
    }

    private function makeStaffUser(string $role, array $permissions): User
    {
        return User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => $role,
            'permissions' => $permissions,
            'is_active' => true,
        ]);
    }

    private function makeOrder(?int $branchId = null, ?int $deliveryUserId = null): Order
    {
        return Order::create([
            'order_type' => $deliveryUserId ? 'delivery' : 'pickup',
            'branch_id' => $branchId,
            'delivery_user_id' => $deliveryUserId,
            'customer_name' => 'Test Customer',
            'customer_phone' => '01000000000',
            'address_line' => 'Test Address',
            'area' => 'Test Area',
            'subtotal' => 100,
            'delivery_fee' => 0,
            'total' => 100,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);
    }
}
