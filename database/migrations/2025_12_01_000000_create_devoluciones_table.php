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
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reserva_id');
            $table->dateTime('fecha_hora_devolucion');
            $table->unsignedBigInteger('usuario_recibe_id')->nullable(); // Usuario que recibe el vehículo (admin/recepcionista)
            $table->json('condiciones_vehiculo')->nullable(); // Checks de condiciones del vehículo
            $table->json('condiciones_accesorios')->nullable(); // Checks de condiciones de accesorios
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'completada', 'con_danos'])->default('pendiente');
            $table->timestamps();

            $table->foreign('reserva_id')->references('id')->on('reservas')->onDelete('cascade');
            $table->foreign('usuario_recibe_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};


