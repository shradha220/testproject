<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserSimno extends Eloquent
{
     protected $table = 'cm4_user_simno';


     protected $fillable = array(
		 'userid',
		 'contact_no',
		 'sim_number'
		 );
     
}
