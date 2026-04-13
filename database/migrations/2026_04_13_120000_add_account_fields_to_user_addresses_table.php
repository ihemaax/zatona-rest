<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->after('label');
            $table->string('phone')->nullable()->after('recipient_name');
            $table->string('district')->nullable()->after('area');
            $table->string('street')->nullable()->after('district');
            $table->string('building')->nullable()->after('street');
            $table->string('floor')->nullable()->after('building');
            $table->string('apartment')->nullable()->after('floor');
            $table->string('landmark')->nullable()->after('apartment');
            $table->text('notes')->nullable()->after('landmark');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'phone',
                'district',
                'street',
                'building',
                'floor',
                'apartment',
                'landmark',
                'notes',
            ]);
        });
    }
};
