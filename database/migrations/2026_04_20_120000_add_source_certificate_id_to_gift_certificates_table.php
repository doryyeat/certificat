<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->foreignId('source_certificate_id')
                ->nullable()
                ->after('template_id')
                ->constrained('gift_certificates')
                ->nullOnDelete();
            $table->index('source_certificate_id');
        });
    }

    public function down(): void
    {
        Schema::table('gift_certificates', function (Blueprint $table) {
            $table->dropIndex(['source_certificate_id']);
            $table->dropConstrainedForeignId('source_certificate_id');
        });
    }
};

