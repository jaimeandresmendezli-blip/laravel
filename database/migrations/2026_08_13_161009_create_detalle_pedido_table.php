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
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->integer('id_detalle')->autoIncrement();
            $table->integer('id_pedido');
            $table->integer('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->unique(['id_pedido', 'id_producto'], 'unique_producto_pedido');

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
