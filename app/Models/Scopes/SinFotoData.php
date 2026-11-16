<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Evita traer users.foto_data (la foto en base64) en cada consulta.
 *
 * Solo actúa cuando la consulta no eligió columnas: un `select(...)` o
 * `addSelect(...)` explícito se respeta. Para leer la foto usa
 * `User::query()->select([... 'foto_data'])` o `withoutGlobalScope`.
 */
class SinFotoData implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($builder->getQuery()->columns === null) {
            $tabla = $model->getTable();
            $builder->select(array_map(fn ($c) => "{$tabla}.{$c}", User::COLUMNAS_SIN_FOTO));
        }
    }
}
