<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4DialNumber extends Eloquent
{
     protected $table = 'cm4_dial_number';


     protected $fillable = array('count', 'prefix','number','used_flag','update_count');
     
}
