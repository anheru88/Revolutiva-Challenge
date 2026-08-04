<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Provider;

/**
 * Resultado de procesar un PayIn a través de un adaptador de proveedor.
 * Contiene el payload enviado y el recibido para auditoría.
 */
final class ProviderResult
{
    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public function __construct(
        public readonly bool $successful,
        public readonly array $request,
        public readonly array $response,
    ) {}

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public static function success(array $request, array $response): self
    {
        return new self(true, $request, $response);
    }

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public static function failure(array $request, array $response): self
    {
        return new self(false, $request, $response);
    }
}
