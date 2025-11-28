<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionContrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'archivo',
        'nombre_original',
        'mime',
        'updated_by',
    ];

    public function usuarioActualiza()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function activa(): ?self
    {
        return self::latest()->first();
    }
}






