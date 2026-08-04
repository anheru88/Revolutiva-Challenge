<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_in_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pay_in_id')->constrained('pay_ins')->cascadeOnDelete();
            $table->string('previous_status')->nullable();
            $table->string('current_status');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_in_status_history');
    }
};
