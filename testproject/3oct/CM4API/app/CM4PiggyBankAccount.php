<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4PiggyBankAccount extends Eloquent
{
     protected $table = 'piggy_bank_ac';
     protected $fillable = [ 'uid','bank_name','bank_ifsc_code','account_number','total_tansfered','total_withdraw','amt_earned','created_at','updated_at'];


}
