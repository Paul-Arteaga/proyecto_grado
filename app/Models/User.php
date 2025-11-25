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
        'name',
        'numero_carnet',
        'email',
        'foto',
        'id_rol',
        'password',
        'carnet_anverso',
        'carnet_reverso',
        'licencia_anverso',
        'licencia_reverso',
        'licencia_fecha_vencimiento',
        'documentos_verificados',
        'documentos_verificados_at',
        'activo',
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
        'licencia_fecha_vencimiento' => 'date',
        'documentos_verificados' => 'boolean',
        'documentos_verificados_at' => 'datetime',
        'activo' => 'boolean',
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
            'rol'      => $this->rol->nombre ?? null,
        ];
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->id_rol === 1;
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole(string $rolNombre): bool
    {
        return $this->rol && $this->rol->nombre === strtolower($rolNombre);
    }

    /**
     * Relación con Reservas
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'user_id');
    }

    /**
     * Relación con Notificaciones (notificaciones que recibe este usuario)
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'user_id');
    }

    /**
     * Verifica si el usuario tiene documentos verificados y vigentes
     */
    public function tieneDocumentosVerificados(): bool
    {
        if (!$this->documentos_verificados) {
            return false;
        }

        // Verificar si la licencia no ha expirado
        if ($this->licencia_fecha_vencimiento) {
            return \Carbon\Carbon::parse($this->licencia_fecha_vencimiento)->isFuture();
        }

        return false;
    }

    /**
     * Verifica si necesita subir documentos nuevamente
     */
    public function necesitaSubirDocumentos(): bool
    {
        return !$this->tieneDocumentosVerificados();
    }
}
