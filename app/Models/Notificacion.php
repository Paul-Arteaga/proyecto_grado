<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones'; // Especificar el nombre de la tabla explícitamente

    protected $fillable = [
        'reserva_id',
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
        'leida_por',
        'leida_at',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_at' => 'datetime',
    ];

    // Relaciones
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leidaPor()
    {
        return $this->belongsTo(User::class, 'leida_por');
    }
}
