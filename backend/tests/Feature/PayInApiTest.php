<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\PayInReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PayInApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PayInReferenceSeeder::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
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

    public function test_it_creates_a_pay_in_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/pay-ins', $this->validPayload());

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
    }

    public function test_provider_b_declines_amount_over_limit_and_marks_failed(): void
    {
        $response = $this->postJson('/api/v1/pay-ins', $this->validPayload([
            'provider_code' => 'provider_b',
            'amount' => 2000000,
        ]));

        $response->assertCreated()->assertJsonPath('data.status', 'FAILED');
        $this->assertDatabaseHas('pay_ins', ['status' => 'FAILED']);
    }

    public function test_it_retrieves_a_pay_in_by_uuid(): void
    {
        $uuid = $this->postJson('/api/v1/pay-ins', $this->validPayload())
            ->json('data.uuid');

        $this->getJson("/api/v1/pay-ins/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.uuid', $uuid)
            ->assertJsonPath('data.status', 'PROCESSED');
    }

    public function test_it_returns_404_for_an_unknown_pay_in(): void
    {
        $this->getJson('/api/v1/pay-ins/99999999-9999-4999-8999-999999999999')
            ->assertNotFound();
    }

    public function test_it_returns_422_on_validation_error(): void
    {
        $this->postJson('/api/v1/pay-ins', $this->validPayload(['amount' => 0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    public function test_it_returns_404_for_an_unknown_customer(): void
    {
        $this->postJson('/api/v1/pay-ins', $this->validPayload([
            'customer_uuid' => '99999999-9999-4999-8999-999999999999',
        ]))->assertNotFound();
    }

    public function test_it_returns_404_for_an_unknown_provider_code(): void
    {
        $this->postJson('/api/v1/pay-ins', $this->validPayload([
            'provider_code' => 'provider_x',
        ]))->assertNotFound();
    }

    public function test_it_rejects_an_account_that_does_not_belong_to_the_customer(): void
    {
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
        $this->postJson('/api/v1/pay-ins', $this->validPayload([
            'account_uuid' => $otherAccountUuid,
        ]))->assertStatus(422);
    }
}
