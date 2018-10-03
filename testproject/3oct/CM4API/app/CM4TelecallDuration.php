<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4TelecallDuration extends Eloquent
{
     protected $table = 'telecallduration';
     protected $fillable = [  
		'caller_id',
		'caller_number',
		'calle_id',
		'calle_number',
		'callType',
		'callDate',
		'callduration',
		'phoneNumber'
];
}
