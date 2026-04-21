<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_certificate_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_certificate_id')->constrained('gift_certificates')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount_applied', 10, 2);
            $table->timestamps();

            $table->unique(['gift_certificate_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_certificate_order');
    }
};

