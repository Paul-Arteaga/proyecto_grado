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
            $table->decimal('monto_total', 10, 2)->nullable()->after('estado');
            $table->string('codigo_qr')->nullable()->after('monto_total');
            $table->enum('estado_pago', ['pendiente', 'pagado', 'rechazado'])->default('pendiente')->after('codigo_qr');
            $table->string('documento_carnet')->nullable()->after('estado_pago');
            $table->string('documento_licencia')->nullable()->after('documento_carnet');
            $table->string('comprobante_pago')->nullable()->after('documento_licencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'monto_total',
                'codigo_qr',
                'estado_pago',
                'documento_carnet',
                'documento_licencia',
                'comprobante_pago'
            ]);
        });
    }
};
