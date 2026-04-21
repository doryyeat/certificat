<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE gift_certificates MODIFY code VARCHAR(255) NULL");
    }

    public function down(): void
    {
        // На откате ставим пустые коды на шаблоны, чтобы не нарушить NOT NULL
        DB::statement("UPDATE gift_certificates SET code = CONCAT('TPL-', id) WHERE code IS NULL");
        DB::statement("ALTER TABLE gift_certificates MODIFY code VARCHAR(255) NOT NULL");
    }
};

