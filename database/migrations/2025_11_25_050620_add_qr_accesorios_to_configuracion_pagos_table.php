<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuracion_pagos', function (Blueprint $table) {
            // Agregar nuevo campo para vehículos (si no existe)
            if (!Schema::hasColumn('configuracion_pagos', 'qr_imagen_vehiculos')) {
                $table->string('qr_imagen_vehiculos')->nullable()->after('id');
            }
            // Agregar nuevo campo para accesorios
            $table->string('qr_imagen_accesorios')->nullable()->after('qr_imagen_vehiculos');
        });

        // Copiar datos de qr_imagen a qr_imagen_vehiculos si existe
        if (Schema::hasColumn('configuracion_pagos', 'qr_imagen')) {
            DB::statement('UPDATE configuracion_pagos SET qr_imagen_vehiculos = qr_imagen WHERE qr_imagen IS NOT NULL');
            // Eliminar la columna antigua
            Schema::table('configuracion_pagos', function (Blueprint $table) {
                $table->dropColumn('qr_imagen');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_pagos', function (Blueprint $table) {
            // Restaurar columna original si no existe
            if (!Schema::hasColumn('configuracion_pagos', 'qr_imagen')) {
                $table->string('qr_imagen')->nullable()->after('id');
            }
        });

        // Copiar datos de vuelta
        DB::statement('UPDATE configuracion_pagos SET qr_imagen = qr_imagen_vehiculos WHERE qr_imagen_vehiculos IS NOT NULL');

        Schema::table('configuracion_pagos', function (Blueprint $table) {
            $table->dropColumn(['qr_imagen_vehiculos', 'qr_imagen_accesorios']);
        });
    }
};
