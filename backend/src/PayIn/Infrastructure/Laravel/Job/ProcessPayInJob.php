<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Laravel\Job;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Src\PayIn\Application\UseCase\ProcessPayInHandler;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Adaptador de cola para ProcessPayInHandler (ADR-004).
 *
 * El procesamiento por defecto es síncrono: CreatePayInHandler invoca el caso
 * de uso en línea. Este Job existe para que el salto a asíncrono sea una
 * sustitución de una línea en el caso de uso —
 * `ProcessPayInJob::dispatch($payIn->uuid()->value())` — sin tocar dominio ni
 * aplicación: solo transporta el UUID, y la lógica sigue siendo la misma.
 */
final class ProcessPayInJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $payInUuid) {}

    public function handle(ProcessPayInHandler $handler): void
    {
        $handler->handle(new Uuid($this->payInUuid));
    }
}
