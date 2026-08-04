<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Entity;

use Src\Shared\Domain\ValueObject\Uuid;

final class PaymentMethod
{
    public function __construct(
        private readonly ?int $id,
        private readonly Uuid $uuid,
        private readonly int $accountId,
        private readonly string $type,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function accountId(): int
    {
        return $this->accountId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function belongsToAccount(int $accountId): bool
    {
        return $this->accountId === $accountId;
    }
}
