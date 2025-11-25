<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehiculo_id',
        'km_inicio',
        'km_fin',
        'estado',
        'checks',
        'observaciones',
        'realizado_por',
    ];

    protected $casts = [
        'checks' => 'array',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }
}


