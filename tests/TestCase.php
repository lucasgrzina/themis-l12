<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    /**
     * Corre solo una vez por proceso de test: si la app arrancó apuntando
     * a otra cosa que no sea la base de test dedicada, aborta todo.
     *
     * @var bool
     */
    protected static $verifiedTestDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$verifiedTestDatabase) {
            $this->guardAgainstNonTestDatabase();
            static::$verifiedTestDatabase = true;
        }
    }

    /**
     * Los tests corren dentro de una transacción que se revierte al
     * terminar (DatabaseTransactions), pero un test mal escrito podría
     * hacer commit explícito. Esta app maneja datos reales de clientes
     * de un estudio jurídico -- confirmar que jamás corremos contra esa
     * base, pase lo que pase con la transacción.
     */
    protected function guardAgainstNonTestDatabase()
    {
        $database = config('database.connections.mysql.database');

        if ($database !== 'themis_l12_test') {
            throw new \RuntimeException(
                "Los tests deben correr contra 'themis_l12_test', no contra '{$database}'. ".
                "Verificá que PHPUnit esté cargando .env.testing (APP_ENV=testing)."
            );
        }
    }
}
