<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4CategoryUser extends Eloquent
{
     protected $table = 'cm4_category_user';


     protected $fillable = array('id', 'category','user_id','order_by','created_at');
     
}
