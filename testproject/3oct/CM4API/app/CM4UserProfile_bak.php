<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserProfile extends Eloquent
{
     protected $table = 'CM4_user_profile';


     protected $fillable = array( 'user_id','user_name','profile_pic','gender','locality',
	  'age','address','country','city','state','lat','long','call_time','about_us','profile_status',
	  'user_rating','marital_status','contact_person','contact_no','referal_code','verfication_status',
	  'device_id','cc_password','email','cc_fdail','category','data_source');
     
}
