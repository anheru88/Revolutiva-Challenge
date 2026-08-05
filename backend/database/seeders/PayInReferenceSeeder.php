<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos de referencia con identificadores fijos para probar la API
 * (Postman / feature tests) sin necesidad de crear entidades previas.
 */
final class PayInReferenceSeeder extends Seeder
{
    public const CUSTOMER_UUID = '11111111-1111-4111-8111-111111111111';

    public const ACCOUNT_UUID = '22222222-2222-4222-8222-222222222222';

    public const PAYMENT_METHOD_UUID = '33333333-3333-4333-8333-333333333333';

    /** Segundo cliente con cuenta propia: sirve para probar la regla de pertenencia. */
    public const OTHER_CUSTOMER_UUID = '44444444-4444-4444-8444-444444444444';

    public const OTHER_ACCOUNT_UUID = '55555555-5555-4555-8555-555555555555';

    public function run(): void
    {
        $customerId = DB::table('customers')->insertGetId([
            'uuid' => self::CUSTOMER_UUID,
            'name' => 'Acme Corp',
            'email' => 'billing@acme.test',
        ]);

        $accountId = DB::table('accounts')->insertGetId([
            'uuid' => self::ACCOUNT_UUID,
            'customer_id' => $customerId,
            'account_number' => 'ACC-0001',
        ]);

        DB::table('payment_methods')->insert([
            'uuid' => self::PAYMENT_METHOD_UUID,
            'account_id' => $accountId,
            'type' => 'card',
        ]);

        $otherCustomerId = DB::table('customers')->insertGetId([
            'uuid' => self::OTHER_CUSTOMER_UUID,
            'name' => 'Other Corp',
            'email' => 'billing@other.test',
        ]);

        DB::table('accounts')->insert([
            'uuid' => self::OTHER_ACCOUNT_UUID,
            'customer_id' => $otherCustomerId,
            'account_number' => 'ACC-9999',
        ]);

        DB::table('payment_providers')->insert([
            ['code' => 'provider_a', 'name' => 'Provider A'],
            ['code' => 'provider_b', 'name' => 'Provider B'],
        ]);
    }
}
