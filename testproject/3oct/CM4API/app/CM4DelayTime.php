<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4DelayTime extends Eloquent
{
     protected $table = 'cm4_delay_time';
     protected $fillable = [  
		'id',
		'user_id',
		'today_date',
		'delay_time',
		'updated_at',
		'created_at'
		
		];
}
