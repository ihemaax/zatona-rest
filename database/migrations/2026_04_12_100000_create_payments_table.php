<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40)->default('cash');
            $table->string('provider_reference')->nullable()->index();
            $table->longText('payment_key')->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('EGP');
            $table->longText('callback_payload')->nullable();
            $table->longText('webhook_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
