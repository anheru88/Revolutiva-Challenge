<?php

declare(strict_types=1);

namespace Src\PayIn\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Src\PayIn\Application\Command\CreatePayInCommand;

final class CreatePayInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_uuid' => ['required', 'uuid'],
            'account_uuid' => ['required', 'uuid'],
            'payment_method_uuid' => ['required', 'uuid'],
            'provider_code' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ];
    }

    public function toCommand(): CreatePayInCommand
    {
        /** @var array<string, mixed> $data */
        $data = $this->validated();

        return new CreatePayInCommand(
            customerUuid: (string) $data['customer_uuid'],
            accountUuid: (string) $data['account_uuid'],
            paymentMethodUuid: (string) $data['payment_method_uuid'],
            providerCode: (string) $data['provider_code'],
            amount: (int) $data['amount'],
            currency: strtoupper((string) $data['currency']),
        );
    }
}
