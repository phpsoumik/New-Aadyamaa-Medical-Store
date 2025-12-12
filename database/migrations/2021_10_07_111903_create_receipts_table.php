<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no');
            $table->integer('transaction_id');
            $table->date('receipt_date');
            $table->date('receipt_due_date');
            $table->string('payment_method');
            $table->string('description');
            $table->integer('profile_id');
            $table->decimal('total_discount');
            $table->decimal('gross_total');
            $table->decimal('total_tax1');
            $table->decimal('total_tax2');
            $table->decimal('total_tax3');
            $table->decimal('total_tax');
            $table->decimal('total_bill');
            $table->string('type');
            $table->integer('created_by');
            $table->string('status');
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
        Schema::dropIfExists('receipts');
    }
}
