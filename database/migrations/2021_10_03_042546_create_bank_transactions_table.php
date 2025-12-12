<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id');
            $table->string('receipt_no');
            $table->string('transaction_no');
            $table->date('receipt_date');
            $table->integer('bank_id');
            $table->integer('account_id');
            $table->string('account_head');
            $table->decimal('amount');
            $table->string('description');
            $table->string('status');
            $table->string('type');
            $table->string('entity');
            $table->integer('branch_id');
            $table->integer('created_by');
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
        Schema::dropIfExists('bank_transactions');
    }
}
