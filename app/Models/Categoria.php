<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidad_pasajeros',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones 1:N
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'categoria_id');
    }

    public function tarifas()
    {
        return $this->hasMany(Tarifa::class, 'categoria_id');
    }

    // Scope para filtrar solo las activas
    public function scopeActivas($q)
    {
        return $q->where('activo', true);
    }
}

