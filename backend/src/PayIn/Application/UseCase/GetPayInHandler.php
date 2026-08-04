<?php

declare(strict_types=1);

namespace Src\PayIn\Application\UseCase;

use Src\PayIn\Application\Query\PayInReadRepository;
use Src\PayIn\Application\Query\PayInResponse;
use Src\Shared\Domain\Exception\EntityNotFoundException;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Consulta una transacción PayIn por su identificador público (UUID).
 */
final class GetPayInHandler
{
    public function __construct(
        private readonly PayInReadRepository $payInReader,
    ) {}

    public function handle(string $uuid): PayInResponse
    {
        return $this->payInReader->findByUuid(new Uuid($uuid))
            ?? throw EntityNotFoundException::withUuid('PayIn', $uuid);
    }
}
