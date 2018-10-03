<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserFeadback extends Eloquent
{
     protected $table = 'cm4_contact_us';
     protected $fillable = [  
		'uid',
		'contact_no',
		'contact_person',
		'subject',
		'comments',
		'app_version',
		'created_at'
		];
}
