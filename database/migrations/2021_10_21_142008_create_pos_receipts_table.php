<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id');
            $table->string('receipt_no');
            $table->decimal('discount');
            $table->integer('profile_id');
            $table->string('payment_method');
            $table->decimal('total_gross_amt');
            $table->decimal('total_bill');
            $table->decimal('total_tendered');
            $table->decimal('total_change');
            $table->decimal('total_tax1');
            $table->decimal('total_tax2');
            $table->decimal('total_tax3');
            $table->decimal('total_tax');
            $table->string('description')->nullable();
            $table->string('doctor_details')->nullable();
            $table->string('patient_details')->nullable();
            $table->string('bill_no')->nullable();
            $table->integer('created_by');
            $table->date('receipt_date');
            $table->string('return_receipt')->nullable();
            $table->string('type');
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
        Schema::dropIfExists('pos_receipts');
    }
}
