<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->id();

            // Campos simples (sin foreign key)
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_permiso')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permisos');
    }
};
