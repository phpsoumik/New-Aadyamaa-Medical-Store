<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingFieldsToRequestedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requested_items', function (Blueprint $table) {
            $table->enum('order_status', ['pending', 'received', 'delivered', 'cancelled'])->default('pending')->after('status');
            $table->date('received_date')->nullable()->after('order_status');
            $table->date('delivered_date')->nullable()->after('received_date');
            $table->unsignedBigInteger('stock_id')->nullable()->after('delivered_date');
            
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requested_items', function (Blueprint $table) {
            $table->dropForeign(['stock_id']);
            $table->dropColumn(['order_status', 'received_date', 'delivered_date', 'stock_id']);
        });
    }
}