<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4FbUsers extends Eloquent
{
     protected $table = 'cm4_fb_details';
     protected $fillable = [  
		'uid',
		'contact_no',
		'fb_id',
		'fb_name',
		'fb_profile_pic',
		'fb_birthday',
		'fb_gender',
		'created_at',
		'updated_at'
		];
}
