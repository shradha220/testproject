<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserInfo extends Eloquent
{
     protected $table = 'CM4_user_info';


     protected $fillable = array('id', 'phone','c_code','device_id','status','code','latitude','longitude','city','state','city1','state1');
     
}
