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
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('carnet_anverso')->nullable()->after('documento_carnet');
            $table->string('carnet_reverso')->nullable()->after('carnet_anverso');
            $table->string('licencia_anverso')->nullable()->after('documento_licencia');
            $table->string('licencia_reverso')->nullable()->after('licencia_anverso');
            $table->date('licencia_fecha_vencimiento')->nullable()->after('licencia_reverso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'carnet_anverso',
                'carnet_reverso',
                'licencia_anverso',
                'licencia_reverso',
                'licencia_fecha_vencimiento',
            ]);
        });
    }
};
