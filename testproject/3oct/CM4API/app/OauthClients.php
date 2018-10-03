<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Model as Eloquent;

class OauthClients extends Eloquent 
{
     protected $collection = 'oauth_clients';
}
