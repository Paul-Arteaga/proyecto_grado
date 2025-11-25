<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPago extends Model
{
    protected $fillable = [
        'qr_imagen_vehiculos',
        'qr_imagen_accesorios',
        'instrucciones_pago',
        'numero_cuenta',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Obtiene la configuración activa (solo debe haber una)
     */
    public static function activa()
    {
        return static::where('activo', true)->first() ?? static::first();
    }
}
