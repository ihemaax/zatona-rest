<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Digital Menu');
            $table->string('slug')->unique()->default('main-menu');
            $table->string('subtitle')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('show_prices')->default(true);
            $table->boolean('show_descriptions')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_menu_settings');
    }
};