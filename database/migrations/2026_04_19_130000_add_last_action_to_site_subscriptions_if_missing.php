<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_subscriptions')) {
            return;
        }

        Schema::table('site_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('site_subscriptions', 'last_action')) {
                $table->string('last_action', 100)->nullable()->after('admin_note');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_subscriptions')) {
            return;
        }

        Schema::table('site_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('site_subscriptions', 'last_action')) {
                $table->dropColumn('last_action');
            }
        });
    }
};
