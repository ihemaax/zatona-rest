<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash','paymob') NOT NULL DEFAULT 'cash'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash') NOT NULL DEFAULT 'cash'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE orders ALTER COLUMN payment_method SET DEFAULT 'cash'");
        }
    }
};
