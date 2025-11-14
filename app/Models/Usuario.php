<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // por si usas eliminación lógica (deleted_at)

class Usuario extends Model
{
    use HasFactory, SoftDeletes;

    // Si tu tabla se llama "usuarios"
    protected $table = 'usuarios';

    // Campos que se pueden llenar en create() o update()
    protected $fillable = [
        'username',
        'email',
        'password',
        'foto',
        'id_rol',
        'title', // opcional, si lo agregas en tu BD
    ];

    // Campos que no quieres mostrar en consultas
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relación: un usuario pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    // Método opcional para mostrar info personalizada
    public function toShow()
    {
        return [
            'nombre' => $this->username,
            'email' => $this->email,
            'rol' => optional($this->rol)->nombre ?? 'Sin rol asignado',
        ];
    }
}
