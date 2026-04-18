<?php

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
        Schema::create('register_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('form_of_own');
            $table->string('contact');
            $table->string('phone');
            $table->string('address');
            $table->string('bank_info');
            $table->string('unp');
            $table->string('email');
            $table->string('password');
            $table->enum('status', ['accepted', 'rejected', 'pending'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_requests');
    }
};
