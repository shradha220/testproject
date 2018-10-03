<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserminEarned extends Eloquent
{
     protected $table = 'cm4_earned_min';
     protected $fillable = [  
		'uid',
		'earned_min',
		'remaining_min',
		'created_at',
		'updated_at'
];
}
