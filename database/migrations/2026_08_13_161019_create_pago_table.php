<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->integer('id_pago')->autoIncrement();
            $table->integer('id_pedido');
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'nequi']);
            $table->decimal('monto', 10, 2);
            $table->enum('estado_pago', ['pendiente', 'pagado', 'rechazado'])->default('pendiente');
            $table->string('referencia_pago', 100)->nullable();
            $table->dateTime('fecha')->useCurrent();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
