<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4ExistingNo extends Eloquent
{
     protected $table = 'cm4_existing_no';
     protected $fillable = [  
		'uid',
		'contact_no',
		'promoter_id',
		'surveyor_id',
		'created_at'
];
}
