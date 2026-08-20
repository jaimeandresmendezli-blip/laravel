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
        Schema::create('usuario', function (Blueprint $table) {
            $table->integer('id_usuario')->autoIncrement();
            $table->integer('id_rol')->default(2); // 2 = Cliente por defecto
            $table->string('correo', 100)->unique();
            $table->string('password_hash', 255);
            $table->string('nombre', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->dateTime('fecha_registro')->useCurrent();

            $table->foreign('id_rol')->references('id_rol')->on('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
