<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehiculo_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'monto_total',
        'codigo_qr',
        'estado_pago',
        'documento_carnet',
        'carnet_anverso',
        'carnet_reverso',
        'documento_licencia',
        'licencia_anverso',
        'licencia_reverso',
        'licencia_fecha_vencimiento',
        'comprobante_pago',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'licencia_fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(\App\Models\Vehiculo::class, 'vehiculo_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    /**
     * Relación con accesorios (muchos a muchos)
     */
    public function accesorios()
    {
        return $this->belongsToMany(Accesorio::class, 'reserva_accesorios')
                    ->withPivot('cantidad', 'precio_unitario', 'precio_total', 'estado', 'comprobante_pago')
                    ->withTimestamps();
    }

    /**
     * Relación con devoluciones
     */
    public function devolucion()
    {
        return $this->hasOne(Devolucion::class, 'reserva_id');
    }

    /**
     * Calcula el monto total incluyendo accesorios
     */
    public function getMontoTotalConAccesoriosAttribute()
    {
        $montoBase = $this->monto_total ?? 0;
        $montoAccesorios = $this->accesorios()
            ->wherePivot('estado', '!=', 'rechazado')
            ->sum('reserva_accesorios.precio_total');
        
        return $montoBase + $montoAccesorios;
    }

    /**
     * Calcula el número de días de la reserva
     */
    public function getDiasReservaAttribute()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) {
            return 0;
        }
        try {
            return \Carbon\Carbon::parse($this->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($this->fecha_fin)) + 1;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
