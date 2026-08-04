<?php

declare(strict_types=1);

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed(PayInReferenceSeeder::class);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validPayload(array $overrides = []): array
{
    return array_merge([
        'customer_uuid' => PayInReferenceSeeder::CUSTOMER_UUID,
        'account_uuid' => PayInReferenceSeeder::ACCOUNT_UUID,
        'payment_method_uuid' => PayInReferenceSeeder::PAYMENT_METHOD_UUID,
        'provider_code' => 'provider_a',
        'amount' => 15000,
        'currency' => 'USD',
    ], $overrides);
}

it('creates a pay-in and returns 201', function (): void {
    $response = $this->postJson('/api/v1/pay-ins', validPayload());

    $response->assertCreated()
        ->assertJsonPath('data.status', 'PROCESSED')
        ->assertJsonPath('data.provider_code', 'provider_a')
        ->assertJsonPath('data.amount', 15000)
        ->assertJsonPath('data.currency', 'USD')
        ->assertJsonStructure([
            'data' => [
                'uuid', 'customer_uuid', 'account_uuid', 'payment_method_uuid',
                'provider_code', 'amount', 'currency', 'status',
                'provider_request', 'provider_response', 'created_at', 'updated_at',
            ],
        ]);

    $this->assertDatabaseHas('pay_ins', ['status' => 'PROCESSED', 'amount' => 15000]);
    // CREATED -> VALIDATED -> PROCESSED
    $this->assertDatabaseCount('pay_in_status_history', 3);
});

it('marks the pay-in FAILED when provider_b declines an over-limit amount', function (): void {
    $response = $this->postJson('/api/v1/pay-ins', validPayload([
        'provider_code' => 'provider_b',
        'amount' => 2000000,
    ]));

    $response->assertCreated()->assertJsonPath('data.status', 'FAILED');
    $this->assertDatabaseHas('pay_ins', ['status' => 'FAILED']);
});

it('retrieves a pay-in by uuid', function (): void {
    $uuid = $this->postJson('/api/v1/pay-ins', validPayload())->json('data.uuid');

    $this->getJson("/api/v1/pay-ins/{$uuid}")
        ->assertOk()
        ->assertJsonPath('data.uuid', $uuid)
        ->assertJsonPath('data.status', 'PROCESSED');
});

it('returns 404 for an unknown pay-in', function (): void {
    $this->getJson('/api/v1/pay-ins/99999999-9999-4999-8999-999999999999')
        ->assertNotFound();
});

it('returns 422 on a validation error', function (): void {
    $this->postJson('/api/v1/pay-ins', validPayload(['amount' => 0]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('amount');
});

it('returns 404 for an unknown customer', function (): void {
    $this->postJson('/api/v1/pay-ins', validPayload([
        'customer_uuid' => '99999999-9999-4999-8999-999999999999',
    ]))->assertNotFound();
});

it('returns 404 for an unknown provider code', function (): void {
    $this->postJson('/api/v1/pay-ins', validPayload([
        'provider_code' => 'provider_x',
    ]))->assertNotFound();
});

it('rejects an account that does not belong to the customer', function (): void {
    // A second customer with its own account, unrelated to the seeded customer.
    $otherCustomerId = DB::table('customers')->insertGetId([
        'uuid' => '44444444-4444-4444-8444-444444444444',
        'name' => 'Other Corp',
        'email' => 'other@corp.test',
    ]);
    $otherAccountUuid = '55555555-5555-4555-8555-555555555555';
    DB::table('accounts')->insert([
        'uuid' => $otherAccountUuid,
        'customer_id' => $otherCustomerId,
        'account_number' => 'ACC-9999',
    ]);

    // Seeded customer + foreign account -> business rule violation (422).
    $this->postJson('/api/v1/pay-ins', validPayload([
        'account_uuid' => $otherAccountUuid,
    ]))->assertStatus(422);
});
