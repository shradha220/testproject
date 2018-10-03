<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Userphonebook extends Eloquent
{
     protected $table = 'cm4_user_phonebook';


     protected $fillable = array('uploader_id','contact_no','contact_person','profile_pic','callme4_status');
     
}
