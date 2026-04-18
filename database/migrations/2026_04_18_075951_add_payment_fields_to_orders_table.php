<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->string('payment_id')->nullable()->after('paid_at');
            $table->string('recipient_name')->nullable()->after('notes');
            $table->string('recipient_email')->nullable()->after('recipient_name');
            $table->text('message')->nullable()->after('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_id', 'recipient_name', 'recipient_email', 'message']);
        });
    }
};
