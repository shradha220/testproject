<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UsersDetails extends Eloquent
{
     protected $table = 'cm4_user_basic_info';


     protected $fillable = array('id', 'uid','gender','location','contact_no','parentProfession','childProfession','Profession_id','details','created_at','updated_at');
     
}
