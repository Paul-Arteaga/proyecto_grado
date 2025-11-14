<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('placa', 20)->unique();       // ABC-123
            $table->string('vin', 30)->nullable()->unique();

            // Descriptivos
            $table->string('marca', 60);
            $table->string('modelo', 60);
            $table->smallInteger('anio')->nullable();
            $table->string('color', 30)->nullable();

            // Foto del vehículo (ruta dentro de storage/public/vehiculos)
            $table->string('foto')->nullable();

            // Técnico / estado
            $table->string('transmision', 10);           // 'Manual' | 'Automática'
            $table->integer('km_actual')->default(0);
            $table->decimal('precio_diario', 10, 2)->default(0);  // 👈 precio
            $table->string('combustible', 20)->nullable();

            // Relación con categoría (1:N)
            $table->foreignId('categoria_id')
                  ->nullable()
                  ->constrained('categorias')
                  ->nullOnDelete();

            // Estado operativo (usado en DisponibilidadController)
            $table->enum('estado', [
                'disponible', 'reservado', 'bloqueado', 'mantenimiento', 'inactivo'
            ])->default('disponible');

            $table->text('observaciones')->nullable();

            $table->softDeletes(); // papelera
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};

