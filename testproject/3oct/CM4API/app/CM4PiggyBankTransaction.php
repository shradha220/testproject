<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4PiggyBankTransaction extends Eloquent
{
     protected $table = 'piggy_bank_transaction';
     protected $fillable = [ 'id','uid','previous_bal','contact_no','transfered_amt','transaction_date','avail_bal','request_amt','total_withdraw','requested_date','created_at','updated_at'];


}
