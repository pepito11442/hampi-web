<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ADMIN = 'admin';
    public const ROL_DOCTOR = 'doctor';
    public const ROL_ENFERMERO = 'enfermero';
    public const ROL_ENFERMERA = 'enfermera';

    /** Roles con acceso al panel administrativo. */
    public const ROLES_ADMIN = ['admin', 'admision'];

    /** Roles que atienden pacientes y tienen horario mensual. */
    public const ROLES_PERSONAL = ['doctor', 'enfermero', 'enfermera'];

    protected $fillable = [
        'name',
        'dni',
        'email',
        'password',
        'role',
        'establecimiento',
        'telefono',
        'especialidad',
        'biografia',
        'titulos',
        'experiencia',
        'foto_perfil',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function esAdmin(): bool
    {
        return in_array($this->role, self::ROLES_ADMIN, true);
    }

    public function esPersonal(): bool
    {
        return in_array($this->role, self::ROLES_PERSONAL, true);
    }

    public function getRolLabelAttribute(): string
    {
        return match ($this->role) {
            'admin', 'admision' => 'Administración',
            'doctor' => 'Médico/a',
            'enfermero', 'enfermera' => 'Enfermero/a',
            default => ucfirst((string) $this->role),
        };
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_perfil) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($this->foto_perfil) ? $disk->url($this->foto_perfil) : null;
    }

    public function iniciales(): string
    {
        $partes = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $iniciales = '';

        foreach (array_slice($partes, 0, 2) as $parte) {
            $iniciales .= mb_substr($parte, 0, 1);
        }

        return mb_strtoupper($iniciales !== '' ? $iniciales : 'H');
    }
}
