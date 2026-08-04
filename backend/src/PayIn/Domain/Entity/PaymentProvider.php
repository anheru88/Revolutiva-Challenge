<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Entity;

final class PaymentProvider
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $code,
        private readonly string $name,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }
}
