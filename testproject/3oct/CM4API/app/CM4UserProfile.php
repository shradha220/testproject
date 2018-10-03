<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserProfile extends Eloquent
{
     protected $table = 'cm4_user_profile';


     protected $fillable = array(
		 'user_id',
		 'user_name',
		 'profile_pic',
		 'gender',
		 'locality',
		 'age',
		 'address',
		 'country',
		 'city',
		 'state',
		 'latitude',
		 'longitude',
		 'call_time',
		 'about_us',
		 'profile_status',
	     'user_rating',
		 'marital_status',
		 'contact_person',
		 'contact_no',
		 'referal_code',
		 'verification_status',
	    'device_id',
		 'cc_password',
		 'email',
		 'cc_fdail',
		 'category',
		 'category_ids',
		 'pincode',
		 'file_type',
		 'live_status',
		 'data_source',
		  'updated_at',
		 'created_at',
		 'paid_for',
		 'piggy_bal',
		 'is_installed',
		 'category_json',
		 'user_searchid',
		 'c_code'
		 );
     
}
