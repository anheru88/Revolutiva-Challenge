<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Exception\InvalidStatusTransitionException;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

final class PayInTest extends TestCase
{
    private function makePayIn(): PayIn
    {
        return PayIn::create(
            uuid: Uuid::random(),
            customerId: 1,
            accountId: 1,
            paymentMethodId: 1,
            paymentProviderId: 1,
            amount: Money::of(15000, 'USD'),
        );
    }

    public function test_create_starts_in_created_and_records_the_initial_transition(): void
    {
        $payIn = $this->makePayIn();

        $this->assertSame(PayInStatus::CREATED, $payIn->status());

        $transitions = $payIn->pullRecordedTransitions();
        $this->assertCount(1, $transitions);
        $this->assertNull($transitions[0]->previous);
        $this->assertSame(PayInStatus::CREATED, $transitions[0]->current);
    }

    public function test_happy_path_transitions_created_validated_processed(): void
    {
        $payIn = $this->makePayIn();
        $payIn->markValidated();
        $payIn->markProcessed(['req' => 1], ['res' => 2]);

        $this->assertSame(PayInStatus::PROCESSED, $payIn->status());
        $this->assertSame(['req' => 1], $payIn->providerRequest());
        $this->assertSame(['res' => 2], $payIn->providerResponse());

        $transitions = $payIn->pullRecordedTransitions();
        $this->assertCount(3, $transitions);
        $this->assertSame(PayInStatus::VALIDATED, $transitions[1]->current);
        $this->assertSame(PayInStatus::PROCESSED, $transitions[2]->current);
    }

    public function test_pull_transitions_clears_the_buffer(): void
    {
        $payIn = $this->makePayIn();

        $this->assertCount(1, $payIn->pullRecordedTransitions());
        $this->assertCount(0, $payIn->pullRecordedTransitions());
    }

    public function test_it_rejects_an_invalid_transition(): void
    {
        $payIn = $this->makePayIn();

        $this->expectException(InvalidStatusTransitionException::class);

        // CREATED -> PROCESSED is not allowed (must pass through VALIDATED).
        $payIn->markProcessed([], []);
    }

    public function test_it_can_fail_from_validated(): void
    {
        $payIn = $this->makePayIn();
        $payIn->markValidated();
        $payIn->markFailed(['req' => 1], ['status' => 'declined']);

        $this->assertSame(PayInStatus::FAILED, $payIn->status());
    }
}
