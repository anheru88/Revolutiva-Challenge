<?php

declare(strict_types=1);

use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Uuid;

it('generates a valid random uuid', function (): void {
    expect(Uuid::random()->value())->toMatch(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i'
    );
});

it('rejects an invalid uuid', function (): void {
    new Uuid('123');
})->throws(InvalidArgumentException::class);

it('compares by value', function (): void {
    $value = '11111111-1111-4111-8111-111111111111';

    expect((new Uuid($value))->equals(new Uuid($value)))->toBeTrue()
        ->and(Uuid::random()->equals(Uuid::random()))->toBeFalse();
});
