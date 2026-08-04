<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Src\PayIn\Application\Provider\ProviderResolver;
use Src\PayIn\Application\Provider\UnsupportedProviderException;
use Src\PayIn\Infrastructure\Provider\FakeProviderAAdapter;
use Src\PayIn\Infrastructure\Provider\FakeProviderBAdapter;

final class ProviderResolverTest extends TestCase
{
    private function resolver(): ProviderResolver
    {
        return new ProviderResolver([
            new FakeProviderAAdapter,
            new FakeProviderBAdapter,
        ]);
    }

    public function test_it_resolves_a_registered_adapter(): void
    {
        $resolver = $this->resolver();

        $this->assertSame('provider_a', $resolver->resolve('provider_a')->code());
        $this->assertSame('provider_b', $resolver->resolve('provider_b')->code());
        $this->assertTrue($resolver->supports('provider_a'));
    }

    public function test_it_throws_for_an_unknown_provider(): void
    {
        $this->expectException(UnsupportedProviderException::class);

        $this->resolver()->resolve('provider_x');
    }
}
