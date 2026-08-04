<?php

declare(strict_types=1);

use Src\Shared\Domain\Exception\InvalidArgumentException;
use Src\Shared\Domain\ValueObject\Email;

it('accepts a valid email', function (): void {
    expect(new Email('billing@acme.test'))->value()->toBe('billing@acme.test');
});

it('rejects an invalid email', function (): void {
    new Email('not-an-email');
})->throws(InvalidArgumentException::class);
