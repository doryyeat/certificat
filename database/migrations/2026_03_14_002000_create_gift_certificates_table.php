<?php

use App\Models\GiftCertificate;
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
        Schema::create('gift_certificates', function (Blueprint $table) {
            $table->id();

            // Связи
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();

            // Основная информация
            $table->string('code')->unique();
            $table->string('title', 255)->default('Подарочный сертификат');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('currency', 3)->default('BYN');

            // Категория и срок действия
            $table->enum('category', ['horeca', 'retail', 'services'])->default('services');
            $table->integer('validity_days')->default(365);
            $table->text('terms_of_use')->nullable();

            // Статус и сроки
            $table->string('status')->default(GiftCertificate::STATUS_DRAFT);
            $table->timestamp('expires_at')->nullable();

            // Информация о получателе
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('notes')->nullable();

            // Кто создал
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Индексы для оптимизации
            $table->index('code');
            $table->index('status');
            $table->index('expires_at');
            $table->index('organization_id');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_certificates');
    }
};
