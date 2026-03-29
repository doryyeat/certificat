<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('inn');
            $table->string('address')->nullable()->after('phone');
            $table->text('description')->nullable()->after('address');
            $table->string('website')->nullable()->after('description');
            $table->string('logo')->nullable()->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'description', 'website', 'logo']);
        });
    }
};
