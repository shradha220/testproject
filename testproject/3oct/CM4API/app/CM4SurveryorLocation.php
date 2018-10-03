<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4SurveryorLocation extends Eloquent
{
     protected $table = 'surveyor_current_loc';
     protected $fillable = [  
		'surveyor_id',
		'promoter_id',
		'current_address',
		'current_latitude',
		'current_longitude',
		'created_at',
		'working_time'
		];
}
