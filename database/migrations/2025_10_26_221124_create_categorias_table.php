<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();        // UQ según documentación
            $table->text('descripcion')->nullable();        // nullable
            $table->unsignedTinyInteger('capacidad_pasajeros')->default(0); // byte
            $table->string('imagen')->nullable();           // ruta del archivo
            $table->boolean('activo')->default(true);       // estado activo/inactivo
            $table->timestamps();                           // created_at y updated_at
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
