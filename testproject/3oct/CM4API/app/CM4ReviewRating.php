<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4ReviewRating extends Eloquent
{
     protected $table = 'cm4_rating_review';
     protected $fillable = ['given_by_uid', 'given_to_uid','given_by_contact','given_to_contact','rating','comments','type','call_id'];
}
