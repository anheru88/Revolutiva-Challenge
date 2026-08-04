<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Exception;

use Src\Shared\Domain\Exception\DomainException;

/**
 * Se lanza cuando se viola una regla de negocio del PayIn
 * (por ejemplo, una account que no pertenece al customer indicado).
 */
final class BusinessRuleViolationException extends DomainException {}
