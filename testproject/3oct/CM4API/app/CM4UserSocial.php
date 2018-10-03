<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4UserSocial extends Eloquent
{
     protected $table = 'cm4_user_social_info';
     protected $fillable = [  
		'uid',
		'youtube_link',
		'facebook_link',
		'twitter_link',
		'instagram_link',
		'snapchat_link',
		'blog_link',
		'msg_bf_call',
		'more_about',
		'created_at',
		'updated_at',
		];
}
