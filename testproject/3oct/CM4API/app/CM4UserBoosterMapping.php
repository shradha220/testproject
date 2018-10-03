<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserBoosterMapping extends Eloquent
{
     protected $table = 'cm4_user_booster_mapping';
     protected $fillable = [ 'id','uid','bid','created_at','updated_at'];


}
