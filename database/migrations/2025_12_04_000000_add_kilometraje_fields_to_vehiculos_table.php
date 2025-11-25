<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->integer('km_inicial')->default(0)->after('km_actual');
            $table->integer('km_ultimo_mantenimiento')->default(0)->after('km_inicial');
        });

        // Inicializar con kilometraje actual existente
        DB::table('vehiculos')->update([
            'km_inicial' => DB::raw('km_actual'),
            'km_ultimo_mantenimiento' => DB::raw('km_actual'),
        ]);
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn(['km_inicial', 'km_ultimo_mantenimiento']);
        });
    }
};


