<?php

namespace App\Support;

use Illuminate\Database\Connectors\MySqlConnector;
use PDO;

/**
 * Conector MySQL para conexiones persistentes (DB_PERSISTENT=true).
 *
 * Laravel ejecuta "SET NAMES …, sql_mode …" cada vez que pide la conexión,
 * aunque PDO devuelva una conexión persistente ya configurada: con la base
 * en otra región eso es un viaje de red extra en cada petición. Aquí esa
 * sentencia va en MYSQL_ATTR_INIT_COMMAND, que MySQL ejecuta solo al abrir
 * la conexión física; la conexión reutilizada conserva esos ajustes.
 *
 * Sin conexión persistente se comporta exactamente como el de Laravel.
 */
class ConectorMySql extends MySqlConnector
{
    /** sql_mode estricto de Laravel para MySQL 8 (también válido en 5.7). */
    private const MODO_ESTRICTO = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

    public function connect(array $config)
    {
        if (empty($config['options'][PDO::ATTR_PERSISTENT]) || isset($config['isolation_level'])) {
            return parent::connect($config);
        }

        if ($inicial = $this->sentenciaInicial($config)) {
            $config['options'][PDO::MYSQL_ATTR_INIT_COMMAND] = $inicial;
        }

        return $this->createConnection($this->getDsn($config), $config, $this->getOptions($config));
    }

    /** Las mismas sentencias que MySqlConnector::configureConnection(). */
    private function sentenciaInicial(array $config): ?string
    {
        $sentencias = [];

        if (isset($config['charset'])) {
            $sentencias[] = isset($config['collation'])
                ? sprintf("NAMES '%s' COLLATE '%s'", $config['charset'], $config['collation'])
                : sprintf("NAMES '%s'", $config['charset']);
        }

        if (isset($config['timezone'])) {
            $sentencias[] = sprintf("time_zone='%s'", $config['timezone']);
        }

        $modo = match (true) {
            isset($config['modes']) => implode(',', $config['modes']),
            ! isset($config['strict']) => null,
            ! $config['strict'] => 'NO_ENGINE_SUBSTITUTION',
            default => self::MODO_ESTRICTO,
        };

        if ($modo !== null) {
            $sentencias[] = sprintf("SESSION sql_mode='%s'", $modo);
        }

        return $sentencias === [] ? null : 'SET '.implode(', ', $sentencias);
    }
}
