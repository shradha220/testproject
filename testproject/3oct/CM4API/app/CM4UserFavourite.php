<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserFavourite extends Eloquent
{
     protected $table = 'cm4_user_favourite';
     protected $fillable = [ 'id','uid','favid','created_at','updated_at'];


}
