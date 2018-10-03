<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4MobileVerify extends Eloquent 
{
     protected $table = 'cm4_userverification';
     protected $fillable = ['mobile','activation_code','is_activated','mobile_info'];
}
