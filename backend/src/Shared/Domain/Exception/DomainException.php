<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exception;

use RuntimeException;

/**
 * Base para todas las excepciones de dominio.
 *
 * El dominio lanza estas excepciones sin conocer HTTP; la traducción a
 * códigos de respuesta se realiza en la capa de infraestructura
 * (bootstrap/app.php).
 */
class DomainException extends RuntimeException {}
