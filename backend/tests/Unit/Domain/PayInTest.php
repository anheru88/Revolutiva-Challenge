<?php

declare(strict_types=1);

use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Enum\PayInStatus;
use Src\PayIn\Domain\Exception\InvalidStatusTransitionException;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

function makePayIn(): PayIn
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

it('starts in CREATED and records the initial transition', function (): void {
    $payIn = makePayIn();

    expect($payIn->status())->toBe(PayInStatus::CREATED);

    $transitions = $payIn->pullRecordedTransitions();
    expect($transitions)->toHaveCount(1)
        ->and($transitions[0]->previous)->toBeNull()
        ->and($transitions[0]->current)->toBe(PayInStatus::CREATED);
});

it('follows the happy path CREATED -> VALIDATED -> PROCESSED', function (): void {
    $payIn = makePayIn();
    $payIn->markValidated();
    $payIn->markProcessed(['req' => 1], ['res' => 2]);

    expect($payIn->status())->toBe(PayInStatus::PROCESSED)
        ->and($payIn->providerRequest())->toBe(['req' => 1])
        ->and($payIn->providerResponse())->toBe(['res' => 2]);

    $transitions = $payIn->pullRecordedTransitions();
    expect($transitions)->toHaveCount(3)
        ->and($transitions[1]->current)->toBe(PayInStatus::VALIDATED)
        ->and($transitions[2]->current)->toBe(PayInStatus::PROCESSED);
});

it('clears the transition buffer once pulled', function (): void {
    $payIn = makePayIn();

    expect($payIn->pullRecordedTransitions())->toHaveCount(1)
        ->and($payIn->pullRecordedTransitions())->toHaveCount(0);
});

it('rejects an invalid transition (CREATED -> PROCESSED)', function (): void {
    makePayIn()->markProcessed([], []);
})->throws(InvalidStatusTransitionException::class);

it('can fail from VALIDATED', function (): void {
    $payIn = makePayIn();
    $payIn->markValidated();
    $payIn->markFailed(['req' => 1], ['status' => 'declined']);

    expect($payIn->status())->toBe(PayInStatus::FAILED);
});
