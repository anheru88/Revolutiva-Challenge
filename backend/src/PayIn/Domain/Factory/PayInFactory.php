<?php

declare(strict_types=1);

namespace Src\PayIn\Domain\Factory;

use Src\PayIn\Domain\Entity\Account;
use Src\PayIn\Domain\Entity\Customer;
use Src\PayIn\Domain\Entity\PayIn;
use Src\PayIn\Domain\Entity\PaymentMethod;
use Src\PayIn\Domain\Entity\PaymentProvider;
use Src\Shared\Domain\ValueObject\Money;
use Src\Shared\Domain\ValueObject\Uuid;

/**
 * Factory del agregado PayIn (patrón Factory, PRD §11).
 *
 * Concentra el ensamblado del agregado: genera el UUID público y traduce las
 * entidades ya resueltas a los identificadores internos que el agregado usa
 * para sus relaciones (ADR-005). El caso de uso queda con la orquestación y no
 * con la construcción, y el agregado nace siempre en estado CREATED.
 */
final class PayInFactory
{
    public function forNewTransaction(
        Customer $customer,
        Account $account,
        PaymentMethod $paymentMethod,
        PaymentProvider $provider,
        Money $amount,
    ): PayIn {
        return PayIn::create(
            uuid: Uuid::random(),
            customerId: (int) $customer->id(),
            accountId: (int) $account->id(),
            paymentMethodId: (int) $paymentMethod->id(),
            paymentProviderId: (int) $provider->id(),
            amount: $amount,
        );
    }
}
