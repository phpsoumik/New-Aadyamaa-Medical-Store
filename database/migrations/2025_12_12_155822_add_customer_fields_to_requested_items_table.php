<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerFieldsToRequestedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requested_items', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->string('customer_phone')->nullable()->after('customer_id');
            $table->string('medicine_name')->nullable()->after('customer_phone');
            $table->integer('quantity')->default(1)->after('medicine_name');
            $table->date('order_date')->nullable()->after('quantity');
            $table->decimal('advance_payment', 10, 2)->default(0)->after('order_date');
            $table->boolean('has_advance')->default(false)->after('advance_payment');
            
            $table->foreign('customer_id')->references('id')->on('profilers')->onDelete('set null');
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
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'customer_phone', 
                'medicine_name',
                'quantity',
                'order_date',
                'advance_payment',
                'has_advance'
            ]);
        });
    }
}
