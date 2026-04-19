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
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->after('admin_note')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by_user_id');
            $table->dropColumn('admin_note');
        });
    }
};
