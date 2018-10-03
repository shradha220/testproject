<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserVersion extends Eloquent
{
     protected $table = 'cm4_user_version';
     protected $fillable = [  
		'user_id',
		'user_app_version'
];
}
