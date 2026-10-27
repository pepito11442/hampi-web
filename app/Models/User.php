<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Cuenta de acceso del personal de salud y la administración.
 *
 * Los roles se definen en constantes: administración entra con correo y
 * el personal de salud con DNI + contraseña. Los ciudadanos no tienen
 * cuenta y se identifican solo con su DNI.
 */
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

    /** Turnos mensuales asignados al trabajador. */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    /** Citas que el médico tiene agendadas con sus pacientes. */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    /** Indica si la cuenta puede entrar al panel administrativo. */
    public function esAdmin(): bool
    {
        return in_array($this->role, self::ROLES_ADMIN, true);
    }

    /** Indica si la cuenta pertenece al personal de salud. */
    public function esPersonal(): bool
    {
        return in_array($this->role, self::ROLES_PERSONAL, true);
    }

    /** Etiqueta legible del rol para las vistas. */
    public function getRolLabelAttribute(): string
    {
        return match ($this->role) {
            'admin', 'admision' => 'Administración',
            'doctor' => 'Médico/a',
            'enfermero', 'enfermera' => 'Enfermero/a',
            default => ucfirst((string) $this->role),
        };
    }

    /**
     * URL pública de la foto de perfil.
     *
     * Devuelve null si no hay foto o si el archivo ya no existe en el
     * disco público, para que la vista use las iniciales como respaldo.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_perfil) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($this->foto_perfil) ? $disk->url($this->foto_perfil) : null;
    }

    /** Iniciales del nombre para el avatar de respaldo. */
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
