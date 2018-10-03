<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4VideoCall extends Eloquent
{
     protected $table = 'cm4_video_call';
     protected $fillable = [  
		'id',
		'src',
		'calledstation',
		'sessiontime',
		'sessionbill',
		'starttime',
		'stoptime',
		'per_min_val'
		
		];
}
