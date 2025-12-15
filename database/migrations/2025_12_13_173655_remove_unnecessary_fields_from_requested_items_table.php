<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnnecessaryFieldsFromRequestedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requested_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'description', 'customer_phone']);
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
            $table->string('item_name')->nullable();
            $table->string('description')->nullable();
            $table->string('customer_phone')->nullable();
        });
    }
}
