<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tarifa extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','monto','moneda','categoria_id']; // 👈 agrega categoria_id

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
