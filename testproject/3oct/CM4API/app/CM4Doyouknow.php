<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Doyouknow extends Eloquent
{
     protected $table = 'cm4_do_you_know';
     protected $fillable = [  
		'content',
		'adimage',
		'view_count',
		'is_active'
];
}
