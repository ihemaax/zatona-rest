<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->integer('estimated_delivery_minutes')->nullable()->after('status');
            $table->timestamp('estimated_delivery_at')->nullable()->after('estimated_delivery_minutes');
            $table->text('status_note')->nullable()->after('estimated_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'estimated_delivery_minutes',
                'estimated_delivery_at',
                'status_note',
            ]);
        });
    }
};