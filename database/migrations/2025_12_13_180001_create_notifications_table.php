<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('requested_item_id');
            $table->string('message');
            $table->enum('type', ['medicine_arrived', 'order_ready'])->default('medicine_arrived');
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('profilers')->onDelete('cascade');
            $table->foreign('requested_item_id')->references('id')->on('requested_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}