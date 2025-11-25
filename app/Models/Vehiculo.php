<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'placa',
        'vin',
        'marca',
        'modelo',
        'anio',
        'color',
        'transmision',
        'km_actual',
        'km_inicial',
        'km_ultimo_mantenimiento',
        'precio_diario',
        'combustible',
        'categoria_id',
        'estado',
        'observaciones',
        'foto',
    ];

    protected $casts = [
        'anio' => 'integer',
        'km_actual' => 'integer',
        'km_inicial' => 'integer',
        'km_ultimo_mantenimiento' => 'integer',
        'precio_diario' => 'decimal:2',
    ];

    // Relación con Categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class);
    }
}
