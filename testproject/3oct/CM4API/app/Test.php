<?php

namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class Test extends Eloquent
{
   protected $table = "test";
   protected $fillable = array('firstname', 'lastname');
}
