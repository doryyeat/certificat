<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_certificate_redemptions', function (Blueprint $table) {
            $table->foreignId('purchased_certificate_id')
                ->nullable()
                ->after('gift_certificate_id')
                ->constrained('purchased_certificates')
                ->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE gift_certificate_redemptions MODIFY gift_certificate_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE gift_certificate_redemptions MODIFY gift_certificate_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('gift_certificate_redemptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchased_certificate_id');
        });
    }
};
