<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_customer_leads_page_and_see_customer_data(): void
    {
        $manager = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_MANAGER,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_MANAGER),
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'name' => 'عميل تجريبي',
            'email' => 'customer@example.com',
            'phone' => '201000000001',
            'user_type' => User::TYPE_CUSTOMER,
            'role' => null,
            'permissions' => [],
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('admin.customer-leads.index'));

        $response->assertOk();
        $response->assertSee($customer->name);
        $response->assertSee($customer->email);
        $response->assertSee($customer->phone);
    }

    public function test_owner_cannot_access_customer_leads_page(): void
    {
        $owner = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_OWNER,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_OWNER),
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)
            ->get(route('admin.customer-leads.index'));

        $response->assertForbidden();
    }

    public function test_manager_can_export_customer_leads_excel(): void
    {
        $manager = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_MANAGER,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_MANAGER),
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'عميل 1',
            'email' => 'lead1@example.com',
            'phone' => '201000000002',
            'user_type' => User::TYPE_CUSTOMER,
            'role' => null,
            'permissions' => [],
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('admin.customer-leads.export.excel'));

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }
}
