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
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->integer('id_movimiento')->autoIncrement();
            $table->integer('id_producto');
            $table->enum('tipo_movimiento', ['entrada', 'salida']);
            $table->integer('cantidad');
            $table->string('motivo', 255);
            $table->dateTime('fecha')->useCurrent();

            $table->foreign('id_producto')->references('id_producto')->on('producto')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
