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
        Schema::create('tb_game_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable(); // liên kết với bảng tb_transactions
            $table->unsignedBigInteger('customer_id')->nullable(); // liên kết với bảng customers
            $table->string('game_name', 100);
            $table->string('bet_key', 50);
            $table->string('reference_number', 255);
            $table->decimal('amount', 20, 2)->default(0);
            $table->enum('result', ['win','lose','pending'])->default('pending');
            $table->decimal('reward_amount', 20, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->text('note')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_game_results');
    }
};
