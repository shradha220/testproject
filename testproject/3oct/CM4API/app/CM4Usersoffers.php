<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Usersoffers extends Eloquent
{
     protected $table = 'cm4_user_offers';
     protected $fillable = [  
		'uid',
		'per_min_val',
		'offer_rate',
		'offer_start_date',
		'offer_end_date',
		'is_active',
		'created_at',
		'updated_at',
		'created_by',
		'color'
		];
}
