<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Promocodes extends Eloquent
{
     protected $table = 'cm4_promocodes';
     protected $fillable = [  
		'uid',
		'contact_no',
		'promo_code',
		'promo_amt',
		'created_at',
		'updated_at'
		];
}
