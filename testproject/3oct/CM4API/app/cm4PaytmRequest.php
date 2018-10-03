<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class cm4PaytmRequest extends Eloquent
{
     protected $table = 'cm4_paytm_request';
     protected $fillable = [  
		'uid',
		'contact_no',
		'avail_bal',
		'contact_person',
		'paytm_amt_req',
		'paytm_amt_paid',
		'reference_id',
		'created_at',
		'updated_at'
		];
}
