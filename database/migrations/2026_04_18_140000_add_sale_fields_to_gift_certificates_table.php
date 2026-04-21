<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->timestamp('sold_at')->nullable()->after('expires_at');
            $table->foreignId('sold_order_id')
                ->nullable()
                ->after('sold_at')
                ->constrained('orders')
                ->nullOnDelete();

            $table->index('sold_at');
            $table->index('sold_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->dropIndex(['sold_at']);
            $table->dropIndex(['sold_order_id']);
            $table->dropConstrainedForeignId('sold_order_id');
            $table->dropColumn('sold_at');
        });
    }
};

