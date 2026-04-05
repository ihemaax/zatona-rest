<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->default('customer')->after('email');
            }
        });

        DB::table('users')->update([
            'user_type' => 'customer',
        ]);

        DB::table('users')
            ->whereIn('role', ['super_admin', 'owner', 'manager', 'branch_staff', 'cashier', 'kitchen', 'delivery'])
            ->update([
                'user_type' => 'staff',
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type')) {
                $table->dropColumn('user_type');
            }
        });
    }
};