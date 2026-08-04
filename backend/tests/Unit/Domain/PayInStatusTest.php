<?php

declare(strict_types=1);

use Src\PayIn\Domain\Enum\PayInStatus;

it('allows valid transitions', function (): void {
    expect(PayInStatus::CREATED->canTransitionTo(PayInStatus::VALIDATED))->toBeTrue()
        ->and(PayInStatus::CREATED->canTransitionTo(PayInStatus::FAILED))->toBeTrue()
        ->and(PayInStatus::VALIDATED->canTransitionTo(PayInStatus::PROCESSED))->toBeTrue()
        ->and(PayInStatus::VALIDATED->canTransitionTo(PayInStatus::FAILED))->toBeTrue();
});

it('forbids invalid transitions', function (): void {
    expect(PayInStatus::CREATED->canTransitionTo(PayInStatus::PROCESSED))->toBeFalse()
        ->and(PayInStatus::PROCESSED->canTransitionTo(PayInStatus::FAILED))->toBeFalse()
        ->and(PayInStatus::FAILED->canTransitionTo(PayInStatus::PROCESSED))->toBeFalse();
});

it('identifies terminal states', function (): void {
    expect(PayInStatus::PROCESSED->isTerminal())->toBeTrue()
        ->and(PayInStatus::FAILED->isTerminal())->toBeTrue()
        ->and(PayInStatus::CREATED->isTerminal())->toBeFalse()
        ->and(PayInStatus::VALIDATED->isTerminal())->toBeFalse();
});
