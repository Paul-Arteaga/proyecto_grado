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
}
