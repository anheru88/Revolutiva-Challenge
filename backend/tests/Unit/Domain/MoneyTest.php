<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Money;

final class MoneyTest extends TestCase
{
    public function test_it_creates_money_in_minor_units(): void
    {
        $money = Money::of(15000, 'usd');

        $this->assertSame(15000, $money->amount());
        $this->assertSame('USD', $money->currency());
    }

    public function test_it_rejects_negative_amounts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::of(-1, 'USD');
    }

    public function test_it_rejects_invalid_currency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::of(100, 'US');
    }

    public function test_equality_considers_amount_and_currency(): void
    {
        $this->assertTrue(Money::of(100, 'USD')->equals(Money::of(100, 'USD')));
        $this->assertFalse(Money::of(100, 'USD')->equals(Money::of(100, 'EUR')));
        $this->assertFalse(Money::of(100, 'USD')->equals(Money::of(200, 'USD')));
    }
}
