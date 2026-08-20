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
        Schema::create('pedido', function (Blueprint $table) {
            $table->integer('id_pedido')->autoIncrement();
            $table->integer('id_cliente');
            $table->string('direccion_entrega', 255);
            $table->string('barrio', 100);
            $table->string('telefono_entrega', 20);
            $table->string('referencia', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado_entrega', ['pendiente', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
            $table->dateTime('fecha')->useCurrent();
            $table->enum('estado', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2);

            $table->foreign('id_cliente')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido');
    }
};
