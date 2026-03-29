<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('amount'); // amount | service
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('RUB');
            $table->integer('valid_days')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->foreignId('template_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('certificate_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
        });

        Schema::dropIfExists('certificate_templates');
    }
};

