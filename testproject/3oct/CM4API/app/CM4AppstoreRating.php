<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4AppstoreRating extends Eloquent
{
     protected $table = 'cm4_appstore_rating';
     protected $fillable = [  
		'uid',
		'contact_no'
];
}
