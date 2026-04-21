<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. IMPORTAMOS EL TRAIT DE SANCTUM

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    // 2. AÑADIMOS HasApiTokens AQUÍ (Esto soluciona el error de createToken)
    use HasApiTokens, HasFactory, Notifiable;

    // 3. IMPORTANTE: Borramos `public mixed $is_active;`

    // 4. Actualizamos el Fillable con las columnas exactas que vamos a usar
    protected $fillable = [
        'name',
        'email',
        'document_number',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            // Le decimos a Laravel que is_active siempre será tratado como Booleano (true/false)
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
