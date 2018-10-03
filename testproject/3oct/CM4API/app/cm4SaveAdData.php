<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class cm4SaveAdData extends Eloquent
{
     protected $table = 'cm4_generated_ads';


     protected $fillable = array(
		 'my_id',
		 'uid',
		 'contact_no',
		 'contact_person',
		 'callme4_status',
		 'contact_profile_pic',
		 'category',
		 'category_ids',
		 'locality',
		 'user_rating',
		 'categorytext',
		 'category_image',
		 'calls',
		 'type_id',
		 'type',
		 'ad_type',
	     'ad_show_status',
		 'created_at'
		 )
		 ;
		
     
}
