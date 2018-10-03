<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Categories extends Eloquent 
{
     protected $table = 'cm4_categories';
     protected $fillable = ['category_id', 'parent_id','category_name','desc','type','type_id','images'];
}
