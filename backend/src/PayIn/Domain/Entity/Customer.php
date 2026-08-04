<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Entity;

use Src\Shared\Domain\ValueObject\Email;
use Src\Shared\Domain\ValueObject\Uuid;

final class Customer
{
    public function __construct(
        private readonly ?int $id,
        private readonly Uuid $uuid,
        private readonly string $name,
        private readonly Email $email,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }
}
