<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exception;

/**
 * Se lanza cuando un Value Object o entidad recibe datos inválidos
 * (por ejemplo, un email mal formado o un importe negativo).
 */
class InvalidArgumentException extends DomainException {}
