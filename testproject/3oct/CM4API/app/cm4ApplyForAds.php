<?php
namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent ;
class cm4ApplyForAds extends Eloquent
{
     protected $table = 'cm4_apply_for_ads';


     protected $fillable = array('id', 'contact_person','contact_no','email_id','plan_name','plan_val','created_at','updated_at');
     
}
