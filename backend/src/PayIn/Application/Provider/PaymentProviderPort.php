<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Provider;

use Src\PayIn\Domain\Entity\PayIn;

/**
 * Puerto de salida hacia un proveedor de pago.
 *
 * Cada adaptador de proveedor implementa esta interfaz. Incorporar un proveedor
 * nuevo consiste únicamente en añadir un adaptador (ver ADR-003).
 */
interface PaymentProviderPort
{
    /**
     * Código único del proveedor que atiende este adaptador (p. ej. "provider_a").
     */
    public function code(): string;

    public function process(PayIn $payIn): ProviderResult;
}
