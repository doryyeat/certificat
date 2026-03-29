<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['free', 'start', 'pro']);
            $table->enum('status', ['active', 'canceled', 'expired', 'trial'])->default('active');
            $table->decimal('price', 10, 2);
            $table->integer('certificates_limit')->nullable();
            $table->decimal('commission_rate', 5, 2);
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('api_enabled')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
