<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Promoter extends Eloquent
{
     protected $table = 'cm4_promoter';
     protected $fillable = ['name', 'user_id','password','contact_no','address','user_type','refered_by'];
}
