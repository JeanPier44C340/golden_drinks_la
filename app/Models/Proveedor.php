<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'nit',
    'nombre',
    'correo',
    'password_hash',
    'telefono',
    'direccion',
    'estado',
])]
#[Hidden(['password_hash'])]
class Proveedor extends Authenticatable
{
    protected $table = 'proveedores';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

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

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nombre'] ?? null;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['correo'] ?? null;
    }
}
