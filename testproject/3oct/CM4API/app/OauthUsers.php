<?php

namespace App;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Model as Eloquent;

class OauthUsers extends Eloquent 
{
     protected $collection = 'oauth_users';
	 protected $fillable = array( 'username', 'password','first_name','last_name');
}
