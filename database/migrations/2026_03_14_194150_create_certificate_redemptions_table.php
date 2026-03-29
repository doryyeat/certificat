<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('pin_code', 6)->nullable();
            $table->string('qr_data', 255);
            $table->json('verification_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            // Исправленный внешний ключ
            $table->foreignId('gift_certificate_id')->constrained();

            // Индексы
            $table->index('gift_certificate_id');
            $table->index('business_id');
            $table->index('redeemed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_redemptions');
    }
};
