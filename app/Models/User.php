<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'nombre_completo',
    'correo',
    'password_hash',
    'telefono',
    'rol_id',
    'estado',
    'ultimo_acceso',
])]
#[Hidden(['password_hash'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    /**
     * Alias para vistas/controladores de Breeze que usan name.
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['nombre_completo'] ?? null;
    }

    /**
     * Alias para vistas/controladores de Breeze que usan email.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->attributes['correo'] ?? null;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
