<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('generic');
            $table->string('barcode')->nullable();
            $table->integer('type');
            $table->text('description')->nullable();
            $table->string('image');
            $table->integer('brand');
            $table->integer('brand_sector');
            $table->integer('category');
            $table->string('side_effects')->nullable();
            $table->date('expiry_date');
            $table->integer('qty');
            $table->integer('strip_size');
            $table->integer('pack_size');
            $table->decimal('sale_price');
            $table->decimal('purchase_price');
            $table->decimal('mrp');
            $table->string('batch_no');
            $table->decimal('tax_1');
            $table->decimal('tax_2');
            $table->decimal('tax_3');
            $table->decimal('discount_percentage');
            $table->integer('min_stock');
            $table->string('item_location');
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
        Schema::dropIfExists('stocks');
    }
}
