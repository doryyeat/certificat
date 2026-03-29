<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Добавляем начальные данные
        DB::table('segments')->insert([
            ['name' => 'HoReCa', 'slug' => 'horeca', 'sort_order' => 1],
            ['name' => 'Розница', 'slug' => 'retail', 'sort_order' => 2],
            ['name' => 'Услуги', 'slug' => 'services', 'sort_order' => 3],
            ['name' => 'Развлечения', 'slug' => 'entertainment', 'sort_order' => 4],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
