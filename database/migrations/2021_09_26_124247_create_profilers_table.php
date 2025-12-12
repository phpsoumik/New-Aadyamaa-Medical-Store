<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profilers', function (Blueprint $table) {
            $table->id();
            $table->string('account_title');
            $table->string('email_address')->nullable();
            $table->string('contact_no');
            $table->string('national_id')->nullable();
            $table->string('address')->nullable();
            $table->string('description')->nullable();
            $table->string('account_type');
            $table->string('status');
            $table->integer('created_user');
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
        Schema::dropIfExists('profilers');
    }
}
