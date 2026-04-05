<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'assigned_to_delivery_at')) {
                $table->timestamp('assigned_to_delivery_at')->nullable()->after('delivery_user_id');
            }

            if (!Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $table->timestamp('out_for_delivery_at')->nullable()->after('assigned_to_delivery_at');
            }

            if (!Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('orders', 'assigned_to_delivery_at')) {
                $columns[] = 'assigned_to_delivery_at';
            }

            if (Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $columns[] = 'out_for_delivery_at';
            }

            if (Schema::hasColumn('orders', 'delivered_at')) {
                $columns[] = 'delivered_at';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

