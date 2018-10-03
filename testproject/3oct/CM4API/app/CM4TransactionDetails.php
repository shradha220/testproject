<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class CM4TransactionDetails extends Eloquent
{
     protected $table = 'cm4_paytm_transaction';
     protected $fillable = ['contact_no', 'uid','GATEWAYNAME','PAYMENTMODE','TXNDATE','	STATUS','MID','CURRENCY','ORDERID','TXNID','TXNAMOUNT','BANKTXNID','BANKNAME','RESPMSG','RESPCODE','CHECKSUMHASH','created_at','updated_at'];
}
