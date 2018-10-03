<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4SurveryorRecording extends Eloquent
{
     protected $table = 'cm4_surveyor_records';
     protected $fillable = [  
		'uid',
		'contact_no',
		'promoter_id',
		'surveyor_id',
		'audio',
		'image',
		'created_at'
];
}
