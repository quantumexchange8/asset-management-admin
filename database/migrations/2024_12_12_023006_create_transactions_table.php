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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('category')->nullable();
            $table->string('transaction_type')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('transaction_number')->nullable();
            $table->string('to_payment_account_name')->nullable();
            $table->string('to_payment_platform')->nullable();
            $table->string('to_payment_platform_name')->nullable();
            $table->string('to_payment_account_no')->nullable();
            $table->string('to_bank_sub_branch')->nullable();
            $table->string('to_bank_branch_address')->nullable();
            $table->string('txn_hash')->nullable();
            $table->decimal('amount', 13)->nullable();
            $table->decimal('transaction_charges', 13)->nullable();
            $table->string('from_currency')->nullable();
            $table->string('to_currency')->nullable();
            $table->decimal('conversion_rate')->nullable();
            $table->decimal('conversion_amount', 13)->nullable();
            $table->decimal('transaction_amount', 13)->nullable();
            $table->string('fund_type')->nullable();
            $table->decimal('old_wallet_amount', 13)->nullable();
            $table->decimal('new_wallet_amount', 13)->nullable();
            $table->string('status')->nullable()->default('processing');
            $table->string('comment')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamp('approval_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_id')
                ->references('id')
                ->on('wallets')
                ->onUpdate('cascade')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};