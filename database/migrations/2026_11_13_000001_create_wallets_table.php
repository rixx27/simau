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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('account_label')->nullable(); // e.g. BCA Prioritas, Mandiri Utama
            $table->enum('type', ['operational', 'trip', 'saving'])->default('operational');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->decimal('daily_limit', 15, 2)->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
