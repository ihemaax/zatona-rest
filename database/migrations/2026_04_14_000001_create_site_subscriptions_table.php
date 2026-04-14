<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_current')->default(true);
            $table->string('plan_slug')->index();
            $table->string('subscription_status')->default('pending')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->timestamps();

            $table->unique('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_subscriptions');
    }
};
