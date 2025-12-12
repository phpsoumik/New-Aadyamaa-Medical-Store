<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('receipt_id');
            $table->integer('sub_transaction_id');
            $table->integer('qty');
            $table->decimal('price');
            $table->decimal('discount');
            $table->decimal('tax1');
            $table->decimal('tax2');
            $table->decimal('tax3');
            $table->decimal('sub_total');
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
        Schema::dropIfExists('sub_receipts');
    }
}
