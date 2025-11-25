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
        // Actualizar el enum para incluir nuevos estados
        \DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('solicitada', 'pendiente', 'confirmada', 'rechazada', 'cancelada') DEFAULT 'solicitada'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'confirmada'");
    }
};
