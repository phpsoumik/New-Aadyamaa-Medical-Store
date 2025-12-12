<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('address');
            $table->text('description')->nullable();
            $table->text('license_no')->nullable();
            $table->text('email')->nullable();
            $table->text('contact')->nullable();
            $table->string('status');
            $table->string('show_1');
            $table->string('tax_name_1')->nullable();
            $table->decimal('tax_value_1');
            $table->string('required_optional_1');
            $table->integer('link1');
            $table->string('show_2');
            $table->string('tax_name_2')->nullable();
            $table->decimal('tax_value_2');
            $table->string('required_optional_2');
            $table->integer('link2');
            $table->string('show_3');
            $table->string('tax_name_3')->nullable();
            $table->decimal('tax_value_3');
            $table->string('required_optional_3');
            $table->integer('link3');
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
        Schema::dropIfExists('branches');
    }
}
