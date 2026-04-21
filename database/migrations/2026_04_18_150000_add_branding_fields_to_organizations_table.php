<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('branding_logo_path')->nullable()->after('logo_url');
            $table->json('branding_colors')->nullable()->after('branding_logo_path');
            $table->string('branding_background_path')->nullable()->after('branding_colors');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['branding_logo_path', 'branding_colors', 'branding_background_path']);
        });
    }
};

