<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4BlockUser extends Eloquent
{
     protected $table = 'cm4_block_user';

     protected $fillable = array('blocked_by', 'blocked_to','id','created_at','flag_status','block_issue','issue_comment');
     //protected $fillable = array('count', 'prefix','number','used_flag','update_count');
     
}
