<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4TempAppUserData extends Eloquent
{
     protected $table = 'cm4_temp_app_user_data';
     protected $fillable = ['contact_person','profile_pic','uploader_id' ,'profession','profession_ids','work_place','age','gender','about_me',
         'contact_no','start_time','end_time','email','address_source',
         'pincode','locality','city','state','country','address','latitude','longitude','updated_at','is_booster','booster_pack_id','status','uploader_name','uploader_contact','amt_earned','promoter_id','surviour_id','install_date','is_installed'];
}
