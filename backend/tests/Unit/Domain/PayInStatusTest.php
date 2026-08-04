<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\PayIn\Domain\Enum\PayInStatus;

final class PayInStatusTest extends TestCase
{
    public function test_allowed_transitions(): void
    {
        $this->assertTrue(PayInStatus::CREATED->canTransitionTo(PayInStatus::VALIDATED));
        $this->assertTrue(PayInStatus::CREATED->canTransitionTo(PayInStatus::FAILED));
        $this->assertTrue(PayInStatus::VALIDATED->canTransitionTo(PayInStatus::PROCESSED));
        $this->assertTrue(PayInStatus::VALIDATED->canTransitionTo(PayInStatus::FAILED));
    }

    public function test_forbidden_transitions(): void
    {
        $this->assertFalse(PayInStatus::CREATED->canTransitionTo(PayInStatus::PROCESSED));
        $this->assertFalse(PayInStatus::PROCESSED->canTransitionTo(PayInStatus::FAILED));
        $this->assertFalse(PayInStatus::FAILED->canTransitionTo(PayInStatus::PROCESSED));
    }

    public function test_terminal_states(): void
    {
        $this->assertTrue(PayInStatus::PROCESSED->isTerminal());
        $this->assertTrue(PayInStatus::FAILED->isTerminal());
        $this->assertFalse(PayInStatus::CREATED->isTerminal());
        $this->assertFalse(PayInStatus::VALIDATED->isTerminal());
    }
}
