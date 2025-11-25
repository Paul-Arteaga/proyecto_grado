<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE reservas MODIFY COLUMN estado ENUM('solicitada','pendiente','confirmada','rechazada','cancelada','completada') DEFAULT 'solicitada'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE reservas MODIFY COLUMN estado ENUM('solicitada','pendiente','confirmada','rechazada','cancelada') DEFAULT 'solicitada'"
        );
    }
};


