<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_ins', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('payment_provider_id')->constrained('payment_providers');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('status')->index();
            $table->json('provider_request')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_ins');
    }
};
