<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class UsBankAccount extends Eloquent
{
     protected $table = 'us_bank_account';


     protected $fillable = array(
     	'id',
		 'user_id',
		 'account_number',
		 'routing_number',
		 'flag_status',
		 'created_at',
		 'account_holder'
		 );
     
}
