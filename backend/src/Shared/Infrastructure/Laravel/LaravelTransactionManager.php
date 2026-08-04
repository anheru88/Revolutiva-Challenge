<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Laravel;

use Illuminate\Database\ConnectionInterface;
use Src\Shared\Application\TransactionManager;

final class LaravelTransactionManager implements TransactionManager
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function transactional(callable $work): mixed
    {
        return $this->connection->transaction(static fn (): mixed => $work());
    }
}
