<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'devoluciones';

    protected $fillable = [
        'reserva_id',
        'fecha_hora_devolucion',
        'usuario_recibe_id',
        'condiciones_vehiculo',
        'condiciones_accesorios',
        'observaciones',
        'estado',
        'km_retorno',
    ];

    protected $casts = [
        'fecha_hora_devolucion' => 'datetime',
        'condiciones_vehiculo' => 'array',
        'condiciones_accesorios' => 'array',
        'km_retorno' => 'integer',
    ];

    // Relaciones
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function usuarioRecibe()
    {
        return $this->belongsTo(User::class, 'usuario_recibe_id');
    }

    /**
     * Garantiza que la reserva asociada cambie a estado completado
     * cada vez que se registra una devolución.
     */
    protected static function booted()
    {
        static::created(function (Devolucion $devolucion) {
            if ($devolucion->reserva && $devolucion->reserva->estado !== 'completada') {
                $devolucion->reserva->update(['estado' => 'completada']);
            }
        });
    }
}


