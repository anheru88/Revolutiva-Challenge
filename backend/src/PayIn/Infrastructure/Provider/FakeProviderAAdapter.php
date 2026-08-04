<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Provider;

use Src\PayIn\Application\Provider\PaymentProviderPort;
use Src\PayIn\Application\Provider\ProviderResult;
use Src\PayIn\Domain\Entity\PayIn;

/**
 * Adaptador simulado del proveedor "provider_a".
 *
 * No realiza integraciones reales (fuera de alcance, ver PRD §16). Construye un
 * payload de request/response representativo para auditoría y siempre aprueba.
 */
final class FakeProviderAAdapter implements PaymentProviderPort
{
    public function code(): string
    {
        return 'provider_a';
    }

    public function process(PayIn $payIn): ProviderResult
    {
        $request = [
            'provider' => $this->code(),
            'reference' => $payIn->uuid()->value(),
            'amount' => $payIn->amount()->amount(),
            'currency' => $payIn->amount()->currency(),
        ];

        $response = [
            'provider' => $this->code(),
            'status' => 'approved',
            'authorization_code' => 'A-'.strtoupper(substr($payIn->uuid()->value(), 0, 8)),
        ];

        return ProviderResult::success($request, $response);
    }
}
