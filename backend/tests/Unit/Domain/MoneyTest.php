<?php

declare(strict_types=1);

use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Money;

it('creates money in minor units', function (): void {
    $money = Money::of(15000, 'usd');

    expect($money->amount())->toBe(15000)
        ->and($money->currency())->toBe('USD');
});

it('rejects negative amounts', function (): void {
    Money::of(-1, 'USD');
})->throws(InvalidArgumentException::class);

it('rejects an invalid currency', function (): void {
    Money::of(100, 'US');
})->throws(InvalidArgumentException::class);

it('compares by amount and currency', function (): void {
    expect(Money::of(100, 'USD')->equals(Money::of(100, 'USD')))->toBeTrue()
        ->and(Money::of(100, 'USD')->equals(Money::of(100, 'EUR')))->toBeFalse()
        ->and(Money::of(100, 'USD')->equals(Money::of(200, 'USD')))->toBeFalse();
});
