<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UsersReports extends Eloquent
{
     protected $table = 'cm4_users_reporting';
     protected $fillable = [  
		'reportingPersonId',
		'reportedPersonId',
		'reason',
		'note',
		'created_at',
		'updated_at'
		];
}
