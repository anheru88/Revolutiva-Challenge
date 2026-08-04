<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Email;

final class EmailTest extends TestCase
{
    public function test_it_accepts_a_valid_email(): void
    {
        $email = new Email('billing@acme.test');

        $this->assertSame('billing@acme.test', $email->value());
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('not-an-email');
    }
}
