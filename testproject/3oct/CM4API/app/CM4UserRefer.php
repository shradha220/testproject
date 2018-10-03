<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserRefer extends Eloquent
{
     protected $table = 'cm4_user_refers';


     protected $fillable = array(
		 'uid',
		 'refer_code',
		 'earned_by_uid',
		 'earned_amt',
		 'created_at'
		 )
		 ;
		
     
}
