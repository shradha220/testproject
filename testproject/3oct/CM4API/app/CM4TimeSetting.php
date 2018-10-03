<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4TimeSetting extends Eloquent
{
     protected $table = 'create_rate_time';
     protected $fillable = ['uid', 'call_time','online_status','per_min_val'];
}
