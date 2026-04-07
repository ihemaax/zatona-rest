<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_open_audit_logs_page(): void
    {
        $manager = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_MANAGER,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_MANAGER),
            'is_active' => true,
        ]);

        AuditLog::create([
            'event' => 'admin.action',
            'method' => 'GET',
            'path' => '/admin/orders',
            'status_code' => 200,
        ]);

        $response = $this->actingAs($manager)->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Audit Logs');
    }


    public function test_owner_can_open_audit_logs_page(): void
    {
        $owner = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_OWNER,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_OWNER),
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('admin.audit-logs.index'));

        $response->assertOk();
    }

    public function test_branch_staff_cannot_open_audit_logs_page(): void
    {
        $staff = User::factory()->create([
            'user_type' => User::TYPE_STAFF,
            'role' => User::ROLE_BRANCH_STAFF,
            'permissions' => User::defaultPermissionsByRole(User::ROLE_BRANCH_STAFF),
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('admin.audit-logs.index'));

        $response->assertForbidden();
    }

    public function test_audit_middleware_stores_log_record(): void
    {
        $customer = User::factory()->create([
            'user_type' => User::TYPE_CUSTOMER,
            'role' => null,
            'permissions' => [],
            'is_active' => true,
        ]);

        $this->actingAs($customer)->get(route('home'));

        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $customer->id,
            'path' => '/',
            'event' => 'http_request',
        ]);
    }
}
