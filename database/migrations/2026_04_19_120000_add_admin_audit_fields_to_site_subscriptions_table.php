<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_subscriptions', function (Blueprint $table) {
            $table->text('admin_note')->nullable()->after('limits');
            $table->string('last_action', 100)->nullable()->after('admin_note');
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->after('last_action')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by_user_id');
            $table->dropColumn(['last_action', 'admin_note']);
        });
    }
};
