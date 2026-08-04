<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Uuid;

final class UuidTest extends TestCase
{
    public function test_random_generates_a_valid_uuid(): void
    {
        $uuid = Uuid::random();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid->value(),
        );
    }

    public function test_it_rejects_an_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Uuid('123');
    }

    public function test_equality_compares_the_value(): void
    {
        $value = '11111111-1111-4111-8111-111111111111';

        $this->assertTrue((new Uuid($value))->equals(new Uuid($value)));
        $this->assertFalse(Uuid::random()->equals(Uuid::random()));
    }
}
