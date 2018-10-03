<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4VideoShoot extends Eloquent
{
     protected $table = 'cm4_video_shoot';
     protected $fillable = [  
		'uid',
		'contact_person',
		'contact_no',
		'profession',
		'profession_ids',
		'shoot_location',
		'type_of_video',
		'script_idea',
		'gender',
		'about',
		'fb_id',
		'fb_name',
		'fb_email',
		'fb_profile_pic',
		'fb_birthday'
		];
}
