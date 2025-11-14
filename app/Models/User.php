<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Campos asignables en masa */
    protected $fillable = [
        'username',
        'email',
        'foto',
        'id_rol',
        'password',
    ];

    /** Campos ocultos */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Casts (incluye hash automático de password) */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // <- Laravel encripta al asignar/crear/actualizar
    ];

    /** Relación con Rol (si la usas) */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /** Helper de visualización opcional */
    public function toShow(): array
    {
        return [
            'username' => $this->username,
            'email'    => $this->email,
            'foto'     => $this->foto,
            'rol'      => $this->rol->name ?? null,
        ];
    }
}
