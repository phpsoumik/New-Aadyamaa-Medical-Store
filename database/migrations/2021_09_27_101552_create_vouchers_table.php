<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no');
            $table->integer('transaction_id');
            $table->integer('profile_id');
            $table->string('profile_name');
            $table->date('voucher_date');
            $table->string('memo');
            $table->decimal('total_amount');
            $table->integer('created_by');
            $table->string('account_type');
            $table->string('type');
            $table->integer('branch_id');
            $table->string('status');
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
        Schema::dropIfExists('vouchers');
    }
}
