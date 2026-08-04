<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Entity;

use Src\Shared\Domain\ValueObject\Uuid;

final class Account
{
    public function __construct(
        private readonly ?int $id,
        private readonly Uuid $uuid,
        private readonly int $customerId,
        private readonly string $accountNumber,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function accountNumber(): string
    {
        return $this->accountNumber;
    }

    public function belongsToCustomer(int $customerId): bool
    {
        return $this->customerId === $customerId;
    }
}
