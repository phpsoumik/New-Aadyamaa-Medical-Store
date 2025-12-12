<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSubReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_sub_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('pos_receipt_id');
            $table->string('mode');
            $table->integer('stock_id');
            $table->string('item_name');
            $table->string('generic_name');
            $table->string('item_description')->nullable();
            $table->integer('unit');
            $table->integer('total_unit');
            $table->integer('free_unit');
            $table->integer('supplier_bonus');
            $table->string('batch_no');
            $table->integer('pack_size');
            $table->integer('sheet_size');
            $table->decimal('purchase_price');
            $table->decimal('selling_price');
            $table->decimal('mrp');
            $table->string('brand_name');
            $table->string('sector_name');
            $table->string('category_name');
            $table->string('product_type');
            $table->date('expiry_date');
            $table->decimal('item_disc');
            $table->decimal('purchase_disc');
            $table->decimal('after_disc');
            $table->decimal('tax_1');
            $table->decimal('tax_2');
            $table->decimal('tax_3');
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
        Schema::dropIfExists('pos_sub_receipts');
    }
}
