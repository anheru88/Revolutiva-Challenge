<?php

declare(strict_types=1);

use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Provider\UnsupportedProviderException;
use Src\PayIn\Infrastructure\Provider\FakeProviderAAdapter;
use Src\PayIn\Infrastructure\Provider\FakeProviderBAdapter;

function resolver(): ProviderResolver
{
    return new ProviderResolver([
        new FakeProviderAAdapter,
        new FakeProviderBAdapter,
    ]);
}

it('resolves a registered adapter', function (): void {
    $resolver = resolver();

    expect($resolver->resolve('provider_a')->code())->toBe('provider_a')
        ->and($resolver->resolve('provider_b')->code())->toBe('provider_b')
        ->and($resolver->supports('provider_a'))->toBeTrue();
});

it('throws for an unknown provider', function (): void {
    resolver()->resolve('provider_x');
})->throws(UnsupportedProviderException::class);
