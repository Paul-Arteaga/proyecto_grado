<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'stock',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Relación con reservas (muchos a muchos)
     */
    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reserva_accesorios')
                    ->withPivot('cantidad', 'precio_unitario', 'precio_total', 'estado', 'comprobante_pago')
                    ->withTimestamps();
    }
}
