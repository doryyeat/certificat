<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchased_certificate_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pc_id'); // Короткое имя поля

            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->timestamps();

            // Короткое имя индекса
            $table->index('pc_id', 'idx_pct_pc_id');
            $table->index('type', 'idx_pct_type');

            // Короткое имя внешнего ключа
            $table->foreign('pc_id', 'fk_pct_pc')
                ->references('id')
                ->on('purchased_certificates')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchased_certificate_transactions');
    }
};
