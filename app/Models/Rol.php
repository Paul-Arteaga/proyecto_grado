<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    // 👇 Esto indica explícitamente el nombre de la tabla en la BD
    protected $table = 'rols';

    // (opcional) si querés permitir asignación masiva
    protected $fillable = ['nombre'];
}
