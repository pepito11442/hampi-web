<?php

namespace App\Models;

use App\Models\Scopes\SinFotoData;
use App\Support\CachePortal;
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
 * el personal de salud con DNI + contraseña. Los ciudadanos no usan esta
 * tabla: entran con DNI + código (ver Ciudadano).
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

    /**
     * Columnas que se cargan por defecto: todas menos foto_data (ver
     * SinFotoData). Una prueba verifica que coincidan con la tabla.
     */
    public const COLUMNAS_SIN_FOTO = [
        'id', 'name', 'email', 'email_verified_at', 'password', 'role', 'establecimiento',
        'remember_token', 'created_at', 'updated_at', 'dni', 'telefono', 'especialidad',
        'biografia', 'titulos', 'experiencia', 'foto_perfil', 'activo', 'foto_mime', 'foto_version',
    ];

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
        'foto_data',
        'foto_mime',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'foto_data',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SinFotoData);

        // La huella se actualiza sola cuando cambia la foto.
        static::saving(function (User $usuario) {
            if ($usuario->isDirty('foto_data')) {
                $usuario->foto_version = $usuario->foto_data
                    ? substr(md5((string) $usuario->foto_data), 0, 12)
                    : null;
            }
        });
    }

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
     * Primero usa la foto guardada en la base (sobrevive a los reinicios
     * del hosting). Si no hay, intenta el archivo antiguo del disco
     * público. Devuelve null para que la vista use las iniciales.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto_version) {
            return route('fotos.show', ['user' => $this->id, 'version' => $this->foto_version], false);
        }

        if (! $this->foto_perfil) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->exists($this->foto_perfil) ? $disk->url($this->foto_perfil) : null;
    }

    /** Huella corta del contenido de la foto, usada en la URL para la caché. */
    public function fotoVersion(): string
    {
        return (string) $this->foto_version;
    }

    /**
     * Totales del personal por grupo, en una sola consulta agrupada y con
     * caché (se invalida al guardar o borrar cualquier usuario).
     *
     * @return array{todos: int, doctores: int, enfermeros: int}
     */
    public static function conteosPersonal(): array
    {
        return CachePortal::recordar('conteos-personal', function () {
            $porRol = static::query()
                ->whereIn('role', self::ROLES_PERSONAL)
                ->selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role');

            return [
                'todos' => (int) $porRol->sum(),
                'doctores' => (int) ($porRol[self::ROL_DOCTOR] ?? 0),
                'enfermeros' => (int) (($porRol[self::ROL_ENFERMERO] ?? 0) + ($porRol[self::ROL_ENFERMERA] ?? 0)),
            ];
        });
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
