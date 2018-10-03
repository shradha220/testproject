<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4BoosterPack extends Eloquent
{
     protected $table = 'booster_packs';
     protected $fillable = [ 'keywords','result_count','location','latitude','longitude','required_result','amt_declared','created_by','created_at'];


}
