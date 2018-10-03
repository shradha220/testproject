<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4AdRecord extends Eloquent
{
     protected $table = 'cm4_ad_data';
     protected $fillable = [  
		'uid',
		'user_id',
		'my_name',
		'my_contact',
		'my_address',
		'contact_person',
		'contact_no',
		'call_type',
		'call_date',
		'call_time',
		'call_duration',
		'banner1_duration',
		'banner2_duration',
		'banner1_earning',
		'banner2_earning',
		'group_id',
		'ad_response',
		'ad_id',
		'created_at'
		];
}
