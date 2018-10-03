<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_login', function (Blueprint $table) {
            $table->increments('id');
            $table->string('udid');
            $table->string('username')->unique();
            $table->string('password', 250);
            $table->boolean('facebook_status');
            $table->boolean('google_plus_status');
            $table->boolean('twitter_status');
            $table->boolean('email_verified');
            $table->integer('idology_security', 60);
            $table->boolean('idology_security_status');
            $table->string('otp_password', 60);
            $table->string('security_question_id', 60);
            $table->string('security_question_answer', 60);
            $table->rememberToken();
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
         Schema::drop('user_login');
    }
}
