<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id',255);
            $table->string('profile_pic',100);
            $table->string('salution', 60);
            $table->string('first_name',200);
            $table->string('middle_name',200);
            $table->string('last_name',200);
            $table->string('gender',200);
            $table->string('dob', 60);
            $table->string('phone_number',20);
            $table->string('country_id',20);
            $table->string('user_education', 60);
            $table->string('user_type', 60);
            $table->string('user_created_on', 60);
            $table->string('user_modified_on', 60);
            $table->string('user_modified_by', 60);
            $table->string('status', 60);
            
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::drop('user_detail');
    }
}
