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
        Schema::create('detalle_carrito', function (Blueprint $table) {
            $table->integer('id_detalle_carrito')->autoIncrement();
            $table->integer('id_carrito');
            $table->integer('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->unique(['id_carrito', 'id_producto'], 'unique_producto_carrito');

            $table->foreign('id_carrito')->references('id_carrito')->on('carrito')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_carrito');
    }
};
