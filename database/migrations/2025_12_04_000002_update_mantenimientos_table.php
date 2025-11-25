<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->foreignId('vehiculo_id')->after('id')->constrained('vehiculos')->cascadeOnDelete();
            $table->integer('km_inicio')->default(0);
            $table->integer('km_fin')->nullable();
            $table->enum('estado', ['pendiente', 'en_progreso', 'completado'])->default('en_progreso');
            $table->json('checks')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('realizado_por')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropForeign(['vehiculo_id']);
            $table->dropForeign(['realizado_por']);
            $table->dropColumn(['vehiculo_id', 'km_inicio', 'km_fin', 'estado', 'checks', 'observaciones', 'realizado_por']);
        });
    }
};


