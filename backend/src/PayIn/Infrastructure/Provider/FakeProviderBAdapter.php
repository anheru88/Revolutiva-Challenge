<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Provider;

use Src\PayIn\Application\Provider\PaymentProviderPort;
use Src\PayIn\Application\Provider\ProviderResult;
use Src\PayIn\Domain\Entity\PayIn;

/**
 * Adaptador simulado del proveedor "provider_b".
 *
 * Demuestra el camino de rechazo (estado FAILED): rechaza importes que superan
 * un límite simulado, devolviendo igualmente el payload para auditoría.
 */
final class FakeProviderBAdapter implements PaymentProviderPort
{
    /** Límite simulado en la unidad menor de la moneda (10 000.00). */
    private const DECLINE_THRESHOLD = 1_000_000;

    public function code(): string
    {
        return 'provider_b';
    }

    public function process(PayIn $payIn): ProviderResult
    {
        $request = [
            'provider' => $this->code(),
            'reference' => $payIn->uuid()->value(),
            'amount' => $payIn->amount()->amount(),
            'currency' => $payIn->amount()->currency(),
        ];

        if ($payIn->amount()->amount() > self::DECLINE_THRESHOLD) {
            return ProviderResult::failure($request, [
                'provider' => $this->code(),
                'status' => 'declined',
                'reason' => 'amount_exceeds_limit',
            ]);
        }

        return ProviderResult::success($request, [
            'provider' => $this->code(),
            'status' => 'approved',
            'authorization_code' => 'B-'.strtoupper(substr($payIn->uuid()->value(), 0, 8)),
        ]);
    }
}
