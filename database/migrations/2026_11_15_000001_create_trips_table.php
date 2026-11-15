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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 15, 2)->default(0);
            $table->enum('status', ['planned', 'active', 'completed'])->default('active');
            $table->string('cover_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('destination_wallet_id')->constrained('trips')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
        });

        Schema::dropIfExists('trips');
    }
};
