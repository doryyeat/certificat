<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchased_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();
            $table->foreignId('source_certificate_id')->nullable()->constrained('gift_certificates')->nullOnDelete();

            $table->string('code')->unique();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('currency', 3)->default('BYN');
            $table->string('category');
            $table->integer('validity_days');
            $table->text('terms_of_use')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('expires_at');

            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('sold_order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->timestamps();

            $table->index('code');
            $table->index('status');
            $table->index('expires_at');
            $table->index('source_certificate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchased_certificates');
    }
};
