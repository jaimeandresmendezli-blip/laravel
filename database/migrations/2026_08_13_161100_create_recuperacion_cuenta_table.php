<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recuperacion_cuenta', function (Blueprint $table) {
            $table->integer('id_recuperacion')->autoIncrement();
            $table->integer('id_usuario');
            $table->string('codigo', 20);
            $table->dateTime('fecha_expiracion');
            $table->tinyInteger('usado')->default(0);

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recuperacion_cuenta');
    }
};
