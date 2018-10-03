<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4Search extends Eloquent
{
     protected $table = 'cm4_search';
     protected $fillable = ['text','uid','latitude','longitude','record_count','distance','locality'];


}
