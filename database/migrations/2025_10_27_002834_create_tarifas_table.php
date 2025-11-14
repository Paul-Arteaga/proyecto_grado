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
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();

            // Campos básicos (ajusta a tu negocio)
            $table->string('nombre');                 // Ej: "Diaria", "Semanal"
            $table->decimal('monto', 10, 2);          // Ej: 150.00
            $table->string('moneda')->default('BOB'); // Ajusta si usas otra

            // FK a categorias (1 categoría -> muchas tarifas)
            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
