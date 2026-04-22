<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_certificate_transactions', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('gift_certificate_id')
                ->constrained('orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gift_certificate_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};

