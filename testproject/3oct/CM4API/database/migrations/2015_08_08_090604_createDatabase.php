<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_id');
            $table->string('client_secret',600);
            $table->string('redirect_uri',2000);
            $table->string('grant_types', 80);
            $table->string('user_id', 80);
           
        });
    
        
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('access_token',50);
            $table->string('client_id',80);
            $table->timestamps('expires');
            $table->string('scope', 2000);
          
        });
        Schema::create('oauth_authorization_codes', function (Blueprint $table) {
            $table->increments('id');
           $table->string('authorization_code',100);
            $table->string('client_id',60);
            $table->string('user_id', 255);
            $table->string('redirect_uri', 2000);
            $table->timestamps('expires');
            $table->string('scope', 2000);
        });
         Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('refresh_token',100);
            $table->string('client_id',60);
            $table->string('user_id', 255);
            $table->timestamps('expires');
            $table->string('scope', 2000);
        });
        Schema::create('oauth_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('password', 2000);
            $table->string('first_name', 500);
            $table->string('last_name', 500);
          
        });
        Schema::create('oauth_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('scope', 2000);
            $table->boolean('is_default');
          
        });
        
         Schema::create('oauth_jwt', function (Blueprint $table) {
            $table->increments('id');
            $table->string('client_id',100);
            $table->string('subject', 80);
            $table->string('public_key', 2000);
            $table->string('last_name', 500);
          
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
