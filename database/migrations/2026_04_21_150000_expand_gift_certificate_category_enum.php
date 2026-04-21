<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE gift_certificates
            MODIFY category ENUM('horeca','retail','services','sport','entertainment','education')
            NOT NULL DEFAULT 'services'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE gift_certificates
            MODIFY category ENUM('horeca','retail','services')
            NOT NULL DEFAULT 'services'
        ");
    }
};

