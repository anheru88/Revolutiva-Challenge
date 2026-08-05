<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El componente no implementa autenticación (PRD §3): no hay tablas
     * `users` ni `password_reset_tokens`. `sessions` sí se conserva porque el
     * stack de Docker usa SESSION_DRIVER=database.
     *
     * `user_id` se mantiene aunque no haya usuarios: el handler de sesión de
     * Laravel escribe esa columna en cada petición, sin ella el driver falla.
     * Queda sin clave ajena, igual que en el esqueleto original.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
