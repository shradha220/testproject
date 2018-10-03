<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserCalltime extends Eloquent 
{
     protected $table = 'cm4_user_calltime';
     protected $fillable = ['user_id','date','start_time','end_time'];
}
