<?php

declare(strict_types=1);

namespace Src\PayIn\Application\Provider;

/**
 * Selecciona el adaptador de proveedor correspondiente a un código
 * (patrones Factory + Strategy, ver ADR-003).
 */
final class ProviderResolver
{
    /** @var array<string, PaymentProviderPort> */
    private array $adapters = [];

    /**
     * @param  iterable<PaymentProviderPort>  $adapters
     */
    public function __construct(iterable $adapters = [])
    {
        foreach ($adapters as $adapter) {
            $this->register($adapter);
        }
    }

    public function register(PaymentProviderPort $adapter): void
    {
        $this->adapters[$adapter->code()] = $adapter;
    }

    public function resolve(string $code): PaymentProviderPort
    {
        return $this->adapters[$code]
            ?? throw UnsupportedProviderException::withCode($code);
    }

    public function supports(string $code): bool
    {
        return isset($this->adapters[$code]);
    }
}
