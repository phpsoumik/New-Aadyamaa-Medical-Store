<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipt_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('receipt_id');
            $table->integer('transaction_id');
            $table->string('account_no')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('card_balance')->nullable();
            $table->decimal('change');
            $table->string('entry_mode')->nullable();
            $table->string('gift_card_ref')->nullable();
            $table->string('host_response')->nullable();
            $table->string('payment_type');
            $table->decimal('round_off');
            $table->decimal('tendered');
            $table->string('terminal_id');
            $table->decimal('trans_amount');
            $table->date('trans_date');
            $table->string('trans_id')->nullable();
            $table->string('trans_ref')->nullable();
            $table->string('trans_status');
            $table->time('trans_time');
            $table->decimal('trans_total_amount');
            $table->string('trans_type');
            $table->string('source_type');
            $table->string('description')->nullable();
            $table->string('receipt_no');
            $table->integer('created_by');
            $table->integer('branch_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipt_payments');
    }
}
