<?php

declare(strict_types=1);

namespace Src\Shared\Application;

/**
 * Puerto de aplicación para ejecutar trabajo dentro de una transacción atómica.
 * La implementación concreta vive en infraestructura (Laravel DB).
 */
interface TransactionManager
{
    /**
     * @template T
     *
     * @param  callable():T  $work
     * @return T
     */
    public function transactional(callable $work): mixed;
}
