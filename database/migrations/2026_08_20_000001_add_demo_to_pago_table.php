<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pago MODIFY metodo_pago ENUM('efectivo', 'transferencia', 'nequi', 'demo') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pago MODIFY metodo_pago ENUM('efectivo', 'transferencia', 'nequi') NOT NULL");
        }
    }
};