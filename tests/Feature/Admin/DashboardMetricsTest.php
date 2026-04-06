<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_poll_returns_kpis_and_weekly_trend_for_selected_range(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_MANAGER,
            'permissions' => ['view_orders', 'view_all_branches_orders'],
            'is_active' => true,
        ]);

        Order::create([
            'order_type' => 'delivery',
            'customer_name' => 'A',
            'customer_phone' => '01000000001',
            'address_line' => 'Address',
            'area' => 'Area',
            'subtotal' => 100,
            'delivery_fee' => 20,
            'total' => 120,
            'payment_method' => 'cash',
            'status' => 'delivered',
            'out_for_delivery_at' => now()->subMinutes(20),
            'delivered_at' => now(),
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard.poll', ['range' => '7d']));

        $response->assertOk();
        $response->assertJsonStructure([
            'cards' => ['kpis', 'status_breakdown'],
            'weekly_trend',
        ]);
    }

    public function test_dashboard_export_snapshot_downloads_csv(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_MANAGER,
            'permissions' => ['view_orders', 'view_all_branches_orders'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard.export-snapshot', ['range' => 'today']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
