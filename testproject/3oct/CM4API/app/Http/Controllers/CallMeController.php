<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Test;
use App\CM4CategoryUser;
use App\cm4ApplyForAds;
use App\CM4Categories;
use App\CM4UserInfo;
use App\CM4UserProfile;
use App\CM4UserCalltime;
use App\CM4UserSimno;
use App\CM4MobileVerify;
use App\CM4BlockUser;
use App\CM4DialNumber;
use App\CM4TempAppUserData;
use App\CM4PiggyBankAccount;
use App\CM4PiggyBankTransaction;
use App\CM4UserBoosterMapping;
use App\CM4UserBankAccountMapping;
use App\CM4BoosterPack;
use App\CM4Search;
use App\Classes\Sms;
use App\CM4UserFavourite;
use App\CM4Promoter;
use App\CM4ReviewRating;
use App\CM4Userphonebook;
use App\CM4TelecallDuration;
use App\CM4AppstoreRating;
use App\CM4UserVersion;
use App\CM4Doyouknow;
use App\CM4UserminEarned;
use App\CM4ExistingNo;
use App\CM4SurveryorRecording;
use App\CM4SurveryorLocation;
use App\CM4UserFeadback;
use App\CM4VideoShoot;
use App\CM4FbUsers;
use App\CM4AdRecord;
use App\CM4Promocodes;
use App\cm4PaytmRequest;
use App\CM4UserRefer;
use App\cm4SaveAdData;
use App\CM4UsersDetails;
use App\CM4TransactionDetails;
use App\CM4UsersReports;
use App\CM4Usersoffers;
use App\CM4UserSocial;
use App\CM4PremiumUser;
use App\CM4TimeSetting;
class CallMeController extends Controller
{
    public static $ctr=0;

    public function __construct()
    {
        //$this->middleware('oauth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $users = Test::all();
        return $users;
    }

  
  //Update premium to Solr
  public function update_to_premium_search_solr()
	{
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData)) && array_key_exists('comments', $requestData)) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      $user_id=$requestData['uid'];
		$tags="";
		$sql1="select id,category_ids,cc_fdail,user_id,user_name,contact_person,contact_no,profile_pic,category,latitude,longitude,address,locality,call_time,user_searchid from cm4_premium_customer where id='$user_id'";
			$userdata= \ DB::select($sql1);
			if(count($userdata)>0)
			{
			 $details_url = "http://172.16.200.35:8983/solr/premium_search/update?stream.body=%3Cdelete%3E%3Cquery%3Eid:$user_id%3C/query%3E%3C/delete%3E&commit=true";
        $details_url = preg_replace('!\s+!', '+', $details_url);
		$response    = file_get_contents($details_url);
				
				$Doc_Id=$userdata[0]->id;
				$username=$userdata[0]->user_id;
				$cc_fdail=$userdata[0]->cc_fdail;
				$user_name=$userdata[0]->user_name;
				$contact_no=$userdata[0]->contact_no;
				$category=$userdata[0]->category;
				$call_time=$userdata[0]->call_time;
				$contact_person=$userdata[0]->contact_person;
				$profile_pic=$userdata[0]->profile_pic;
				$latitude=$userdata[0]->latitude;
				$longitude=$userdata[0]->longitude;
				$address=$userdata[0]->address;
				$locality=$userdata[0]->locality;
				$user_searchid=$userdata[0]->user_searchid;

				$category_ids=$userdata[0]->category_ids;
					if($category_ids=="")
					{
					$category_ids=0;	
					}
					
					$geolocation=$latitude.",".$longitude;
					$qry="SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($category_ids) and `cm4_categories`.`type_id`=1";
					
					 $gettags= \DB::select($qry);
	
			if(count($gettags)>0)
			{
			$tags=$gettags[0]->tags;
			}	
				 	if($tags=="")
					{
					$tags="Others";	
					}
					if($contact_person=="")
					{
					$contact_person="";	
					}
					
					$update=array(
						'id' => $Doc_Id,
						"user_id" => array(
							'set' => $username
						),
						"call_time" => array(
							'set' => $call_time
						),
						
						"cc_fdail" => array(
							'set' => $cc_fdail
						),
						"live_status" => array(
							'set' => 1
						),
						"contact_person" => array(
							'set' => $contact_person
						),
						"user_name" => array(
							'set' => $user_name
						)
						,
						"contact_no" => array(
							'set' => $contact_no
						)
						,
						"latitude" => array(
							'set' => $latitude
						)
						,
						"longitude" => array(
							'set' => $longitude
						)
						,
						"geolocation" => array(
							'set' => $geolocation
						)
						,
						"category" => array(
							'set' => $category
						)
						,
						"address" => array(
							'set' => isset($address)?$address:""
						)
						,
						"locality" => array(
							'set' => isset($locality)?$locality:""
						)
						,
						"category_ids" => array(
							'set' => isset($category_ids)?$category_ids:"0"
						)
						,
						
						"service" => array(
							'set' => isset($category)?$category:""
						)
						,
						"profile_pic" => array(
							'set' => isset($profile_pic)?$profile_pic:""
						)
						,
						"tags" => array(
							'set' => isset($tags)?$tags:""
						),
						
						"user_searchid" => array(
							'set' => isset($user_searchid)?$user_searchid:""
						),
						
					);
					
				$update = json_encode(array($update));
					
				$ch = curl_init('http://172.16.200.35:8983/solr/premium_search/update?commit=true');
				
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);

				// Return transfert
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Set type of data sent
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				//echo "<pre>"; print_r($output);
				// Get response code
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($responseCode == 200)
				{
					$data = collect(["status" => "1", "message" => 'Updated!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
				
				}
				else
				{
					$data = collect(["status" => "1", "message" => 'Unable to Update!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
					
				}
				return response()->json($data, 200);
			}
		}
		
		
		
		//Update User to Solr
  public function update_to_solr()
	{
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData)) && array_key_exists('comments', $requestData)) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      $user_id=$requestData['uid'];
		
		
		
		// check and find the records from the userprofile table
		$tags="";
		$sql1="select id,category_ids,cc_fdail,user_id,user_name,contact_person,contact_no,profile_pic,category,latitude,longitude,address,locality,call_time,user_searchid from cm4_user_profile where id='$user_id'";
			
			 $userdata= \ DB::select($sql1);
			
			if(count($userdata)>0)
			{
				
			 $details_url = "http://172.16.200.35:8983/solr/search/update?stream.body=%3Cdelete%3E%3Cquery%3Eid:$user_id%3C/query%3E%3C/delete%3E&commit=true";
        $details_url = preg_replace('!\s+!', '+', $details_url);
		$response    = file_get_contents($details_url);
				
				$Doc_Id=$userdata[0]->id;
				$username=$userdata[0]->user_id;
				$cc_fdail=$userdata[0]->cc_fdail;
				$user_name=$userdata[0]->user_name;
				$contact_no=$userdata[0]->contact_no;
				$category=$userdata[0]->category;
				$call_time=$userdata[0]->call_time;
				$contact_person=$userdata[0]->contact_person;
				$profile_pic=$userdata[0]->profile_pic;
				$latitude=$userdata[0]->latitude;
				$longitude=$userdata[0]->longitude;
				$address=$userdata[0]->address;
				$locality=$userdata[0]->locality;
				$user_searchid=$userdata[0]->user_searchid;

				$category_ids=$userdata[0]->category_ids;
					if($category_ids=="")
					{
					$category_ids=0;	
					}
					
					$geolocation=$latitude.",".$longitude;
					$qry="SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($category_ids) and `cm4_categories`.`type_id`=1";
					
					 $gettags= \DB::select($qry);
	
			if(count($gettags)>0)
			{
			$tags=$gettags[0]->tags;
			}	
				 	if($tags=="")
					{
					$tags="Others";	
					}
					if($contact_person=="")
					{
					$contact_person="";	
					}
					
					$update=array(
						'id' => $Doc_Id,
						"user_id" => array(
							'set' => $username
						),
						"call_time" => array(
							'set' => $call_time
						),
						
						"cc_fdail" => array(
							'set' => $cc_fdail
						),
						"live_status" => array(
							'set' => 1
						),
						"contact_person" => array(
							'set' => $contact_person
						),
						"user_name" => array(
							'set' => $user_name
						)
						,
						"contact_no" => array(
							'set' => $contact_no
						)
						,
						"latitude" => array(
							'set' => $latitude
						)
						,
						"longitude" => array(
							'set' => $longitude
						)
						,
						"geolocation" => array(
							'set' => $geolocation
						)
						,
						"category" => array(
							'set' => $category
						)
						,
						"address" => array(
							'set' => isset($address)?$address:""
						)
						,
						"locality" => array(
							'set' => isset($locality)?$locality:""
						)
						,
						"category_ids" => array(
							'set' => isset($category_ids)?$category_ids:"0"
						)
						,
						
						"service" => array(
							'set' => isset($category)?$category:""
						)
						,
						"profile_pic" => array(
							'set' => isset($profile_pic)?$profile_pic:""
						)
						,
						"tags" => array(
							'set' => isset($tags)?$tags:""
						),
						
						"user_searchid" => array(
							'set' => isset($user_searchid)?$user_searchid:""
						),
						
					);
					
				$update = json_encode(array($update));
					
				$ch = curl_init('http://172.16.200.35:8983/solr/search/update?commit=true');
				
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);

				// Return transfert
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Set type of data sent
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				//echo "<pre>"; print_r($output);
				// Get response code
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($responseCode == 200)
				{
					$data = collect(["status" => "1", "message" => 'Updated!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
				
				}
				else
				{
					$data = collect(["status" => "1", "message" => 'Unable to Update!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
					
				}
				return response()->json($data, 200);
			}
		}
	
	   

   /**
     *
     * Update Comments.
     *
     * @return Response
     */
    public function update_comments() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData)) && array_key_exists('comments', $requestData)) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      $uid=$requestData['uid'];
	  $comments=$requestData['comments'];
	  $updated_by=$requestData['updated_by'];
	  $live_update=\DB::table('cm4_user_profile')->where('id', '=',$uid)->update(array('comments'=>$comments,'updated_by'=>$updated_by,'telecaller_update'=>'1'));
	  if($live_update>0)
	   {
	   $data = collect(["status" => "1", "message" => 'Updated!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
	    }
	 else
	    {
            $data = collect([ "status" => "0","message" => 'Already Updated !','errorCode'=>'105','errorDesc'=>'',"data" =>array(),"device_key" => $token]);
        }
	return response()->json($data, 200);
    }
   
   
    /* Fetch Cm4 User by Date.
     *
     * @return Response
     */
    public function Cm4check_updated_user() {
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      
		// $checkdate="SELECT id,user_id,contact_person,contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where date(created_at)='".$selected_date."' and is_installed='1'";
		$checkdate="SELECT id,user_id,contact_person,contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where updated_by in (2,5) and is_installed='1' order by id desc";
	     
		$qrychkdate= \ DB::select($checkdate);
		$current_users=array();
		if(count($qrychkdate)>0)
		{
		foreach($qrychkdate as $val)
		{
		$val->call_status=str_replace(" ","",$val->call_status);
		$val->comments=str_replace(" ","",$val->comments);
		
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where src='".$val->contact_no."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcountquery)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val->callcount=$callcount;
		}
		else
		{
		$val->callcount=$callcount;	
		}
		$checksearch="SELECT id,text FROM `cm4_search` where uid='".$val->id."' order  by id limit 10";
	     $qrysearch= \ DB::select($checksearch);
		if(count($qrysearch)>0)
		{
		$val->search=$qrysearch;	
		}
		else
		{
		$val->search=array();		
		}
		array_push($current_users,$val);
		}
		
		}		
	
	$data = collect(["status" => "1","message" =>"User List.",'errorCode'=>'','errorDesc'=>'','data'=>$current_users,'device_key' => $token]);
      
   return response()->json($data, 200);
    }
   
 /* Fetch Cm4 User Connected.
     *
     * @return Response
     */
    public function cm4_connected_users() {
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      
		// $checkdate="SELECT id,user_id,contact_person,contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where date(created_at)='".$selected_date."' and is_installed='1'";
		$checkdate="SELECT id,user_id,contact_person,contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where updated_by='3' and is_installed='1' order by id desc";
	     
		$qrychkdate= \ DB::select($checkdate);
		$current_users=array();
		if(count($qrychkdate)>0)
		{
		foreach($qrychkdate as $val)
		{
		$val->call_status=str_replace(" ","",$val->call_status);
		$val->comments=str_replace(" ","",$val->comments);
		
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where src='".$val->contact_no."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcountquery)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val->callcount=$callcount;
		}
		else
		{
		$val->callcount=$callcount;	
		}
		$checksearch="SELECT id,text FROM `cm4_search` where uid='".$val->id."' order  by id limit 10";
	     $qrysearch= \ DB::select($checksearch);
		if(count($qrysearch)>0)
		{
		$val->search=$qrysearch;	
		}
		else
		{
		$val->search=array();		
		}
		array_push($current_users,$val);
		}
		
		}		
	
	$data = collect(["status" => "1","message" =>"User List.",'errorCode'=>'','errorDesc'=>'','data'=>$current_users,'device_key' => $token]);
      
   return response()->json($data, 200);
    }
     /*
     * @return Response
     */
    public function Cm4check_live_user_new() {
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('start_date',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $start_date =$requestData['start_date'];
		$end_date =$requestData['end_date'];
		$uid=$requestData['uid'];
		$count=0;
		$checkcount_qry="SELECT count(*) as totaldata FROM `cm4_user_profile` where date(created_at) between '".$start_date."' and '".$end_date."' and is_installed='1'";
	     $checkcount= \ DB::select($checkcount_qry);
		$add_qry="";
		$total=0;
		$offset=0;
		if(count($checkcount)>0)
		{
		$count=$checkcount[0]->totaldata;
		if($uid=='1')
		{
		$total=$count/2;
		if(fmod($total, 1) !== 0.00){
		$total=$total+0.5;
		} 
		else 
		{
		$total=$total;
		}
		
		
		$add_qry="limit $total";	
		}
		else
		{
		$total=$count/2;
		if(fmod($total, 1) !== 0.00){
		$total=$total-0.5;
		$offset=$total+1;
		} 
		else 
		{
		$total=$total;
		$offset=$total;
		}
		$add_qry="limit $total offset $offset";		
		}		
		}
		
		
		$checkdate="SELECT id,user_id,contact_person,SUBSTR(contact_no,-10) as contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where date(created_at) between '".$start_date."' and '".$end_date."' and is_installed='1' $add_qry";
		//$checkdate="SELECT id,user_id,contact_person,contact_no,telecaller_update,created_at,comments,updated_by as call_status FROM `cm4_user_profile` where contact_no='9873851557' and is_installed='1'";
	    $qrychkdate= \ DB::select($checkdate);
		$current_users=array();
		if(count($qrychkdate)>0)
		{
		foreach($qrychkdate as $val)
		{
		$callcount=0;
		$val->call_status=str_replace(" ","",$val->call_status);
		$val->comments=str_replace(" ","",$val->comments);
		$querycategory="SELECT count(*) as totalcount from cc_call where src='".$val->contact_no."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcountquery)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val->callcount=$callcount;
		}
		else
		{
		$val->callcount=$callcount;	
		}
		$checksearch="SELECT id,text FROM `cm4_search` where uid='".$val->id."' limit 5";
	     $qrysearch= \ DB::select($checksearch);
		if(count($qrysearch)>0)
		{
		$val->search=$qrysearch;	
		}
		else
		{
		$val->search=array();		
		}
		array_push($current_users,$val);
		}
		
		}		
	
	$data = collect(["status" => "1","message" =>"User List.",'errorCode'=>'','errorDesc'=>'','data'=>$current_users,'device_key' => $token]);
      
   return response()->json($data, 200);
    }
	
	
  
  /**
     *
     * Update Status of user whether he or she is online or not.
     *
     * @return Response
     */
    public function update_online_status() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
     $uid=$requestData['uid'];
	  $online_status=$requestData['status'];
	  
	  $premium_update=\DB::table('cm4_premium_customer')->where('id', '=',$uid)->update(array('online_status'=>$online_status,));
      $live_update=\DB::table('cm4_user_profile')->where('id', '=',$uid)->update(array('is_callback'=>$online_status));
	  if($live_update>0)
	   {
	   $data = collect(["status" => "1", "message" => 'Updated!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
	    }
	 else {
            $data = collect([ "status" => "0","message" => 'Already Updated !','errorCode'=>'105','errorDesc'=>'',"data" =>array(),"device_key" => $token]);
        }

        return response()->json($data, 200);
    }


 /**
     *
     * Cancel Offer Status
     *
     * @return Response
     */
    public function cancel_offer() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('offer_id', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      
	  $offer_id=$requestData['offer_id'];
	  $status=$requestData['status'];
	  
	  $offer_cancel=\DB::table('cm4_user_offers')->where('id', '=',$offer_id)->update(array('status' =>$status,'is_active'=>2));
     
	  if($status=='Stop')
	  {
	  $msg='Your Offer has been Stopped';	  
	  }
	  else
	  {
	$msg='Your Offer has been Stopped';			
	  }
	  
	  
	  if($offer_cancel)
	   {
	   $data = collect(["status" => "1", "message" => $msg,'errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
	    }
	 else {
            $data = collect([ "status" => "0","message" => $msg,'errorCode'=>'105','errorDesc'=>'',"data" =>array(),"device_key" => $token]);
        }

        return response()->json($data, 200);
    }
 
 /**
     * Update_offer
     *
     * @return Response
     */
	  public function Update_offer()
		{
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData)
            && array_key_exists('offer_rate', $requestData)
            && array_key_exists('offer_start_date', $requestData)
			 && array_key_exists('offer_end_date', $requestData)
			)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $today_date =date('Y-m-d');
		$startdate=$requestData['offer_start_date'];
		$enddate=$requestData['offer_end_date'];
		$uid=$requestData['uid'];
	   $today_date= date('Y-m-d', strtotime($today_date));
	   $offer_start_date=date('Y-m-d',strtotime($requestData['offer_start_date']));
	   $offer_end_date=date('Y-m-d',strtotime($requestData['offer_end_date']));
	   $is_active=0;
	   $offer_id=$requestData['offer_id'];
	 
	    if ($today_date >= $offer_start_date && $today_date <= $offer_end_date)
		{
		  $is_active=1;
		}
		$data = [
                "uid" => $requestData['uid'],
                "per_min_val" => $requestData['per_min_val'],
                "offer_rate" =>$requestData['offer_rate'],
                "offer_start_date" =>$requestData['offer_start_date'],
				"offer_end_date" =>$requestData['offer_end_date'],
				"is_active"=>$is_active,
				"created_by"=>'1'
			   ];
           
			$updateoffers=CM4Usersoffers::where('id','=',$offer_id)->update($data);  
			if($updateoffers)
			{
            $data = collect(["status" => "1","message" => 'Your have successfully Updated!','errorCode'=>'','errorDesc'=>'',"data" =>array(), "device_key" => $token]);
			}
			else
			{
			$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
		
   return response()->json($data, 200);
	}
 
 
 
 
 
 /**
     *
     * SET RATE AND CALL TIME.
     *
     * @return Response
     */
    public function set_Rate_Time() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      $uid=$requestData['uid'];
	  $call_time=$requestData['day_time'];
	  $online_status=$requestData['status'];
	  $per_min_val=$requestData['call_rate'];
	  
	   $matchThese = ['uid' => $requestData['uid']];
	 	$user = CM4TimeSetting::where($matchThese)->get(['id']);
        $status = $user->count();
     $rate_time = [
                "uid" => $uid,
                "call_time" => $call_time,
                "online_status" =>$online_status,
                "per_min_val" =>$per_min_val
				];
	 if ( $status == 0) 
	    {
		$inserttime=CM4TimeSetting::create($rate_time);  
	    }
		else
		{
    	//UPDATE
	$premium_update=CM4TimeSetting::where('uid', '=',$uid)->update($rate_time);
		}
			 
	  $premium_update=\DB::table('cm4_premium_customer')->where('id', '=',$uid)->update(array('call_time' =>$call_time,'online_status'=>$online_status,'per_min_val'=>$per_min_val));
      $live_update=\DB::table('cm4_user_profile')->where('id', '=',$uid)->update(array('call_time' =>$call_time,'is_callback'=>$online_status,'per_min_val'=>$per_min_val));
	  
	   $ch = curl_init();
	 $URL='https://www.callme4.com:8443/CM4API/update_to_solr';
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("uid" =>$uid)));
    curl_setopt($ch, CURLOPT_POST, 1); 
    $resulta = curl_exec($ch);
    if (curl_errno($ch)) {
        //print curl_error($ch);
    } else {
        curl_close($ch);
    }
   
   
    $ch1 = curl_init();
	 $URL='https://www.callme4.com:8443/CM4API/update_to_premium_search_solr';
    curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch1, CURLOPT_URL, $URL);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode(array("uid" =>$uid)));
    curl_setopt($ch1, CURLOPT_POST, 1); 
    $resulta = curl_exec($ch1);
    if (curl_errno($ch1)) {
        //print curl_error($ch);
    } else {
        curl_close($ch1);
    }
   
   
   
	  if($live_update>0)
	   {
	   $data = collect(["status" => "1", "message" => 'Updated!','errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
	    }
	 else {
            $data = collect([ "status" => "0","message" => 'Already Updated !','errorCode'=>'105','errorDesc'=>'',"data" =>array(),"device_key" => $token]);
        }

        return response()->json($data, 200);
    }


  /**
     *
     * Fetch OFFER RATES Data.
     *
     * @return Response
     */
    public function Fetch_Offer_rate() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       $uid=$requestData['uid'];
	  $matchThese=[ 'uid' =>$uid];	
	  $records=array();		
			
		 $rec_qry="SELECT * FROM `cm4_user_offers` where uid='".$uid."' order by offer_start_date";
	     $rec= \ DB::select($rec_qry);
		if(count($rec)>0)
		{
		 foreach($rec as $val)
		 {
		 array_push($records,$val);	 
		 }
		 $data = collect(["status" => "1", "message" => 'Rate Offer List!','errorCode'=>'','errorDesc'=>'', "data" =>$records, "device_key" => $token]);
		 }
	 else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"data" =>array(),"device_key" => $token]);
        }

        return response()->json($data, 200);
    }
  
  //Add user Call History
	    public function getusercallhistory() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('calle_id', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
			}
     
					
		$caller_id = $requestData['caller_id'];
		$caller_number=$requestData['caller_number'];
		
		$calle_id=$requestData['calle_id'];
		$calle_number=$requestData['calle_number'];
		
		$callType=$requestData['callType'];
		$callDate=$requestData['callDate'];
		$callduration=$requestData['callduration'];
		
		$phoneNumber=$requestData['phoneNumber'];
		
		//$calldate=date("Y-m-d",strtotime($calldate));
		
		
		
       $durationarray=array('caller_id'=>$caller_id,'caller_number'=>$caller_number,'calle_id'=>$calle_id,'calle_number'=>$calle_number,'callType'=>$callType,'callDate'=>$callDate,'callduration'=>$callduration,'phoneNumber'=>$phoneNumber);
	   
	   $data=CM4TelecallDuration::create($durationarray);
		
        
		if(count($data)>0)
		{
        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>array('last_insert_id'=>$data->id), "device_key" => $token]);
		}
		else
		{
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);	
		}
        return response()->json($result, 200);

    }
	
	
  
/**
     * Report User Request.
     *
     * @return Response
     */
	  public function Create_New_Offer()
		{
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData)
            && array_key_exists('offer_rate', $requestData)
            && array_key_exists('offer_start_date', $requestData)
			 && array_key_exists('offer_end_date', $requestData)
			)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $today_date =date('Y-m-d');
		$startdate=$requestData['offer_start_date'];
		$enddate=$requestData['offer_end_date'];
		$uid=$requestData['uid'];
	   $today_date= date('Y-m-d', strtotime($today_date));
	   $offer_start_date=date('Y-m-d',strtotime($requestData['offer_start_date']));
	   $offer_end_date=date('Y-m-d',strtotime($requestData['offer_end_date']));
	   $is_active=0;
	   $checkdate="SELECT count(*) as num FROM `cm4_user_offers` where (offer_start_date <= '".$enddate."' and offer_end_date >= '".$startdate."') and uid='".$uid."' and is_active!='2'";
	   $qrychkdate= \ DB::select($checkdate);
		if($qrychkdate[0]->num==0)
		{
	    if ($today_date >= $offer_start_date && $today_date <= $offer_end_date)
		{
		  $is_active=1;
		}
		$data = [
                "uid" => $requestData['uid'],
                "per_min_val" => $requestData['per_min_val'],
                "offer_rate" =>$requestData['offer_rate'],
                "offer_start_date" =>$requestData['offer_start_date'],
				"offer_end_date" =>$requestData['offer_end_date'],
				"is_active"=>$is_active,
				"created_by"=>'1',
				"color"=>$requestData['color']
			   ];
           
			$insertrec=CM4Usersoffers::create($data);  
			if($insertrec)
			{
            $data = collect(["status" => "1","message" => 'Offer created successfully!','errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
			else
			{
			$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
		}
		else
		{
		$data = collect([ "status" => "0","message" => 'Date already exist in another offer created by you choose another date!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
		}	
   return response()->json($data, 200);
	}	

  
 /**
     * Report User Request.
     *
     * @return Response
     */
	  public function Report_User_Request()
		{
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('reportedPersonId', $requestData)
            && array_key_exists('reportingPersonId', $requestData)
            && array_key_exists('reason', $requestData)
			)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		$data = [
                "reportingPersonId" => $requestData['reportingPersonId'],
                "reportedPersonId" => $requestData['reportedPersonId'],
                "reason" =>$requestData['reason'],
                "note" => $requestData['note']
			   ];
            $insertrec=CM4UsersReports::create($data);  
			if($insertrec)
			{
            $data = collect(["status" => "1","message" => 'Your report has successfully logged!','errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
			else
			{
			$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
	}	
	/**
     * Update User Basic Details Details.
     *
     * @return Response
     */
    public function Update_User_basic_Details()
    {
        $applyads=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('gender', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('location', $requestData)
			&&array_key_exists('parentProfession', $requestData)
			&&array_key_exists('uid', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		 $data = [
                "uid" => $requestData['uid'],
                "contact_no" => $requestData['contact_no'],
                "gender" =>$requestData['gender'],
                "parentProfession" => $requestData['parentProfession'],
				"location" => $requestData['location'],
				"childProfession" => $requestData['childProfession'],
				"Profession_id" => $requestData['id'],
				"details" => $requestData['detail']

            ];
            $insertrec=CM4UsersDetails::create($data);  
			if($insertrec)
			{
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
			else
			{
			$data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
	}

	 /**
     *
     * Fetch getfacebookdetails Data.
     *
     * @return Response
     */
    public function getfacebookdetails() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       $uid=$requestData['uid'];
	  $matchThese=[ 'uid' =>$uid];			
			$rec=CM4FbUsers::where($matchThese)->get(['id','uid','fb_id','contact_no','fb_name','fb_profile_pic','fb_birthday']);
            $status = $rec->count();
            if($status)
			{		 
		 $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>$rec[0], "device_key" => $token]);
		 }
	 else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"data" =>array(),"device_key" => $token]);
        }

        return response()->json($data, 200);
    }
	
		 /**
     *
     * Fetch getuser_searchid_details Data.
     *
     * @return Response
     */
    public function getuser_searchid_details() {
		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('user_searchid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
      $user_searchid=$requestData['user_searchid'];
	  $matchThese=[ 'user_searchid' =>$user_searchid,'live_status'=>'1'];			
			$rec=CM4UserProfile::where($matchThese)->get(['id']);
            $status = $rec->count();
             if($status)
			{		 
		 $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', 'is_duplicate' =>'1', "device_key" => $token]);
		 }
	 else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),'is_duplicate' =>'0',"device_key" => $token]);
        }
		return response()->json($data, 200);
    }
	
	
	
	/**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
	  $db_ext = \DB::connection('mysql_external');
        $cc_category = $db_ext->table('cc_category')->get();
		return $cc_category;
	}

    /**
     * Show category.
     *
     * @return Response
     */
    public function category() {

       // \Log::info('category list fetch.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }

        if($requestData['uid']!=0){
            $matchThese = ['id' =>$requestData['uid'] ];
            $rec=CM4UserProfile::where($matchThese)->get(['category_ids']);
            $status = $rec->count();
            if($status){
            $ids = $rec[0]["category_ids"];

            }else{
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
                return $result;
            }

        }else{
            $ids=0;
        }
    global $collection;
        $collection = explode(',',$ids);
        $categories = CM4Categories::all(['category_id as id', 'parent_id as parent_id', 'category_name as name','images as images','type as type']);
      
       $categories = $categories->filter(function ($item) {

          return $item->parent_id == '0';
     })->values();
       
        $categoriesWithsegment = $categories->each(function ($item) {
            global  $collection;
           $statusValue=in_array($item->id, $collection);
            $item->status= $statusValue?"1":"0";

            $segment=$this->getChild($item->id);
            if($item->images!='') {
                $item->images = "https://www.callme4.com:8443/api/public/images/" . $item->images;
            }else{
                $item->images = "https://www.callme4.com:8443/api/public/noImage.png";
            }

            $item->sub_cat=$segment;

            $segment->each(function ($item) {
                global  $collection;
                $statusValue=in_array($item->id, $collection);
                $item->status=$statusValue?"1":"0";
                if($item->images!='') {
                    $item->images = "https://www.callme4.com:8443/api/public/images/" . $item->images;
                }else{
                    $item->images = "https://www.callme4.com:8443/api/public/noImage.png";
                }
                $service = $this->getChild($item->id);
               foreach($service as $val){
                   global  $collection;
                   $statusValue=in_array($item->id, $collection);
                   $val->status=$statusValue?"1":"0";
                   $val->grand_parent_id=$item->parent_id;
                   $val->grand_parent_name=$this->getName($item->parent_id);
                   if($val->images!='') {
                       $val->images = "https://www.callme4.com:8443/api/public/images/" . $val->images;
                   }else{
                       $val->images = "https://www.callme4.com:8443/api/public/noImage.png";
                   }
               }
                $item->sub_cat=$service;
       })->values();


        })->values();

        $status = $categoriesWithsegment->count();


        if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $categoriesWithsegment, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    public function solrCategory() {

		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }

        if($requestData['uid']!=0){
            $matchThese = ['id' =>$requestData['uid'] ];
            $rec=CM4UserProfile::where($matchThese)->get(['category_ids']);
            $status = $rec->count();
            if($status){
                $ids = $rec[0]["category_ids"];

            }else{
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
                return $result;
            }

        }else{
            $ids=0;
        }
        global $collection;
        $collection = explode(',',$ids);
         $details_url = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=parent_id%3A0&start=0&rows=200&wt=json&indent=true";

        $details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
         $response_arr;
//return count($response_arr);
        if (count($response_arr)==0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            return $data;
        }
       // return $response_arr;

        $category=[];
        foreach($response_arr as $value)
        {
            global  $collection;

            $value['name']= $value['category_name'];
            unset($value['category_name']);
            unset($value['_version_']);
           $value['group_name']="";
		   // unset($value['category_id']);

            if($value['images']!="") {
                $value['images'] = "https://www.callme4.com:8443/uploaded_file/categorytypesimages/" . $value['images'];
            }else{
                $value['images'] = "https://www.callme4.com:8443/uploaded_file/categorytypesimages/noImage.png";
            }

            $statusValue=in_array($value['id'], $collection);
            $value['status']= $statusValue?"1":"0";






            $segment=$this->get_category($value['category_id']);
          //  return $segment;
            $categorywithsegment=[];

            foreach($segment as $val)
            {
                global  $collection;

                $val['name']= $val['category_name'];
                $val['parent_name']=$value['name'];
				$val['group_name']="";
			   unset($val['category_name']);
                unset($val['_version_']);
                $statusValue=in_array($val['id'], $collection);
                $val['status']= $statusValue?"1":"0";
                //return $val['images'];
                if($val['images']!="") {
                    $val['images'] = "https://www.callme4.com:8443/uploaded_file/categorytypesimages/" . $val['images'];
                }else{
                    $val['images'] = "https://www.callme4.com:8443/uploaded_file/categorytypesimages/noImage.png";
                }
                $service=$this->get_category($val['category_id']);

             /*    $segmentwithservice=[];
                foreach($service as $ser)
                {
                    global  $collection;
                    $ser['name']= $ser['category_name'];
                    $ser['grand_parent_id']=  $value['id'];
                    $ser['grand_parent_name']=  $value['name'];
                    unset($ser['category_name']);
                    unset($ser['_version_']);
                    $statusValue=in_array($ser['id'], $collection);
                    $ser['status']= $statusValue?"1":"0";

                    if($ser['images']!="") {
                        $ser['images'] = "https://www.callme4.com/uploaded_file/categorytypesimages/" . $ser['images'];
                    }else{
                        $ser['images'] = "https://www.callme4.com/uploaded_file/categorytypesimages/noImage.png";
                    }
                    array_push($segmentwithservice,$ser);
                    //return $ser;
                    //  return $ser;
                }
                // return $segmentwithservice;
                $val['sub_cat']=$segmentwithservice;
*/
                array_push($categorywithsegment,$val); 

            }
            $value['sub_cat']=$categorywithsegment;
            array_push($category,$value);
        }
        // return $category ;



        $status = count($category);


        if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $category, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    
	//search services
	
	public function getsearchservice() {

		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }

       /*  if($requestData['uid']!=0){
            $matchThese = ['id' =>$requestData['uid'] ];
            $rec=CM4UserProfile::where($matchThese)->get(['category_ids']);
            $status = $rec->count();
            if($status){
                $ids = $rec[0]["category_ids"];

            }else{
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
                return $result;
            }

        }else{
            $ids=0;
        }
        global $collection;
        $collection = explode(',',$ids); */
		$text=$requestData['servicesearch'];
       
        //$details_url = 'http://172.16.200.35:8983/solr/category/select?q="'.$text.'*"++&fq=type%3A"Service"&start=0&rows=10&lowercaseOperators=true&wt=json&indent=true';
      $details_url='http://172.16.200.35:8983/solr/category/select?q='.$text.'*&fq=type%3AService&wt=json&indent=true&start=0&rows=8';
		
		$details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
         //return $response_arr;
//return count($response_arr);
        if (count($response_arr)==0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            return $data;
        }
       // return $response_arr;

        $category=[];
        foreach($response_arr as $value)
        {
           //global  $collection;

            $value['name']= $value['category_name'];
            unset($value['category_name']);
            unset($value['_version_']);
       
	   $details = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=category_id%3A$value[parent_id]&wt=json&indent=true";
		
        $details = preg_replace('!\s+!', '+', $details);
        $response    = file_get_contents($details);
        $response = json_decode($response, true);

        $response_segment= $response["response"]["docs"];
		    $value['parent_name']=$response_segment[0]['category_name'];
			$getcategory=$response_segment[0]['parent_id'];
	
		$getgrandparent = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=category_id%3A$getcategory&wt=json&indent=true";
		
        $getgrandparent = preg_replace('!\s+!', '+',$getgrandparent);
        $responsegrand    = file_get_contents($getgrandparent);
        $responsegrand = json_decode($responsegrand, true);
		$response_category= $responsegrand["response"]["docs"];
	
	$value['grand_parent_name']=$response_category[0]['category_name'];
	  $value['grand_parent_id']=$response_category[0]['category_id'];
	 
		array_push($category,$value);
			
                
               

            }
            
    


        $status = count($category);


        if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $category, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);


    }
	
	
	//new search services
	
	public function getsearchservicenew() {

		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }

		$text=$requestData['servicesearch'];
       
       $details_url='http://172.16.200.35:8983/solr/category/select?q='.$text.'*&wt=json&indent=true&start=0&rows=10';
		
		$details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
        
        if (count($response_arr)==0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            return $data;
        }
      
       $category=[];
        foreach($response_arr as $value)
        {
           //global  $collection;

            $value['name']= $value['category_name'];
            unset($value['category_name']);
            unset($value['_version_']);
       
	    if($value['type']=='Segment')
		{
		$details = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=category_id%3A$value[parent_id]&wt=json&indent=true";
		
        $details = preg_replace('!\s+!', '+', $details);
        $response    = file_get_contents($details);
        $response = json_decode($response, true);

        $response_category= $response["response"]["docs"];
		    $value['parent_name']=$response_category[0]['category_name'];
			$value['grand_parent_name']="";
			$value['grand_parent_id']="0";
		
		}
		
		else if($value['type']=='Service')
		{
		$details = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=category_id%3A$value[parent_id]&wt=json&indent=true";
		$details = preg_replace('!\s+!', '+', $details);
        $response    = file_get_contents($details);
        $response = json_decode($response, true);

        $response_segment= $response["response"]["docs"];
		    $value['parent_name']=$response_segment[0]['category_name'];
			$getcategory=$response_segment[0]['parent_id'];
	
		$getgrandparent = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=category_id%3A$getcategory&wt=json&indent=true";
		
        $getgrandparent = preg_replace('!\s+!', '+',$getgrandparent);
        $responsegrand    = file_get_contents($getgrandparent);
        $responsegrand = json_decode($responsegrand, true);
		$response_category= $responsegrand["response"]["docs"];
	
	$value['grand_parent_name']=$response_category[0]['category_name'];
	  $value['grand_parent_id']=$response_category[0]['category_id'];
		}
		else
		{
		$value['parent_name']="";
		$value['grand_parent_name']="";
		$value['grand_parent_id']="0";	
		}
	   	array_push($category,$value);
			
        }
            
     $status = count($category);
    if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $category, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);
    }
	
	
	
	
	
	
	
	/**
     *
     * Fetch child Data.
     *
     * @return Response
     */
    public function getChild($pid) {

        $matchThese = ['parent_id' =>$pid ];

        $categories = CM4Categories::where($matchThese)->get(['category_id as id', 'parent_id as parent_id', 'category_name as name','images as images','type as type']);
        $status = $categories->count();
       // $data =['data'=>$categories,'count'=>$status];

        return $categories->count()==0?[]:$categories;


    }

    /**
     *
     * Fetch child Data.
     *
     * @return Response
     */
    public function getName($pid) {

        $matchThese = ['id' =>$pid ];

        $categories = CM4Categories::where($matchThese)->get([ 'category_name as name']);


        return $categories[0]["name"];


    }

    
	   /**
     *
     * Fetch username Data.
     *
     * @return Response
     */
    public function fetchusername() {

		$collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('dialnum', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'dialnum' => $requestData['dialnum']
        ];
        $rules = [
            'dialnum' => 'string'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
      $contactno=$requestData['user_contact_num'];
	  $dailnum=$requestData['dialnum'];
		$data = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
            ->where('passdialnum', '=',$dailnum)->where('phone2', '=',$contactno)->limit(1)->orderBy('id','desc')
            ->orWhere('passdialnum', '=',$dailnum)->where('phone1', '=',$contactno)->limit(1)->orderBy('id','desc')
			->get();
			 if (count($data)>0) {
			if($contactno==$data[0]->phone2)
			{
			$getphoneno=$data[0]->phone1;
			}
			else
			{
				$getphoneno=$data[0]->phone1;
			}
			//echo $getphoneno;die;
			
			$matchThese=[ 'contact_no' => $getphoneno];			
			$rec=CM4UserProfile::where($matchThese)->get(['id','contact_person','user_name','locality','category','profile_pic']);
            $status = $rec->count();
            if($status){
                $contactperson= $rec[0]["contact_person"];
				$username= $rec[0]["user_name"];
				$userid= $rec[0]["id"];
				$locality=$rec[0]["locality"];	
				$category=$rec[0]["category"];
				$profile_pic=$rec[0]["profile_pic"];
				if($contactperson=="")
					{
					$contactperson=$username;	
					}
				 if($profile_pic!='') {
                $rec[0]["profile_pic"] = \Config::get('constants.results.root')."/user_pic/" . $rec[0]["profile_pic"];
            }
		 
		 $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => array('uid'=>$userid,'name'=>$contactperson,'locality'=>$locality,'category'=>$category,'profile_pic'=>$rec[0]["profile_pic"]), "device_key" => $token]);
		}
			 }
	 else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);

	
    }
	
	/**
     * Show the form for fetching the category.
     *
     * @return Response
     */
    public function getCategory() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('pid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'pid' => $requestData['pid']
        ];
        $rules = [
            'pid' => 'required|numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
        $matchThese = ['parent_id' =>$requestData['pid'] ];

            $categories = CM4Categories::where($matchThese)->get(['category_id', 'parent_id', 'category_name']);
            $status = $categories->count();

            if ($status) {

                $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $categories], "device_key" => $token]);
            } else {
                $data = collect(["status" => [ "code" => "105", "message" => \Config::get('constants.results.105')], "device_key" => $token]);
            }
        
        return response()->json($data, 200);

      
    }
 
	//API to get verification Code
	public function GetMobileVerifycode() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('mobile', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'mobile' => $requestData['mobile']
        ];
        $rules = [
            'mobile' => 'required|phone_validator'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
        $matchThese = ['mobile' =>$requestData['mobile']];
		$digits = 4;
		$activation_code=str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
	$getnocount = CM4MobileVerify::where($matchThese)->get(['mobile', 'activation_code','is_activated']);
            $status = 0;

            if ($status>0) {

              //  $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $categories], "device_key" => $token]);
            } 
			
			else {
                
				
				$data = array("mobile" => $requestData['mobile'], "activation_code" =>$activation_code, "is_activated" =>"F", "mobile_info" => $requestData['mobile_info'], "is_ac_created" =>"T", "account_create_time" => $requestData['expirydate']);
            CM4MobileVerify::create($data);
				
				
				
				$data = collect(["status" => [ "code" => "105", "message" => \Config::get('constants.results.105')], "device_key" => $token]);
            }
        
        return response()->json($data, 200);

      
    }

    /**
     * To register a user.
     *
     * @return Response

    public function register()
    {

        $genratedVal = $this->gen_card_with_alias();
        $activation = $this->gen_activation_code();
        $phoneNumber = "919717132393";
        $omobile = '0091' . $phoneNumber;
        $fdial = '91' . $this->convertFdialkey($omobile);
       // return $requestData;


        // return $genratedVal;
        $userId=$genratedVal['user'];
        $useralias=$genratedVal['useralias'];
        $pass=$genratedVal['pass'];
        $loginkey=$genratedVal['loginkey'];
        $mobile=$genratedVal['loginkey'];
         $email='amit110387@gmail.com';

//$userId=3296995172;
        $data = \DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $userId)
            ->get();

        //return $data;
       // return count($data);
        if (count($data) == 0) {

        $data = [
            'creationdate' => date('Y-m-d H:i:s'),
            'expirationdate' => date('Y-m-d H:i:s', (strtotime(date('Y-m-d H:i:s')) + 24 * 3600 * 60)),
            'username' => $userId,
            'useralias' => $useralias,
            'uipass' => $pass,
            'credit' => '1100000000.00000',
            'tariff' => '1',
            'activated' => 'f',
            'status' => '1',
            'lastname' => '',
            'firstname' => '',
            'phone' => $mobile,
            'FDial' => $fdial,
            'email' => $email,
            'nbused' => '1',
            'invoiceday' => '1',
            'activationCode' => $activation,
            'loginkey' => $loginkey
        ];
        $id = \DB::connection('a2billing')->table('cc_card')
            ->insertGetId($data);

        }
        else{
        $id =$data[0]->id;

        }
        /************NOW MAKE ENTRY OTHER TABLES******************

        if($id){
            $matchThese=['user_id'=>$userId];
            $userData = CM4UserProfile::where($matchThese)->get();
            $path='';


            $fullname = "aaaaaaaaaa";
            $created_date = $date = date('Y-m-d H:i:s');
            $value = rand(10, 1000000000);
            $globalid = 'LB' . $value;
            $status = "Hi there! I am using Callme4";
//insert description in cm4_user_profile -


            $data = \DB::connection('a2billing')->table('cc_callerid')
                ->where('id_cc_card', '=', $id)
                ->get();
            if(count($data)==0){
                $data = [
                    'cid' => $mobile,
                    'id_cc_card' => $id,
                    'activated' => 't'
                ];
                 \DB::connection('a2billing')->table('cc_callerid')
                    ->insertGetId($data);


            }



            /* added by subhash to identify user registration from mobile or from web or from backed
            $app_from="";
            $userid="";
            if($app_from!='' && $userid!='')
            {
                // $query_reg=" insert into cm4_user_registration_from set username='$userid', registration_on='$app_name',registration_from='$app_from', add_date=now()";
                $query_reg="insert into cm4_user_registration_from set mobile='$phone',username='$userid', registration_on='$app_name',registration_from='$app_from',mobile_info='$mobile_info',mobile_imei='$mobile_imei',mobile_sim_imsi='$mobile_sim_imsi', add_date=now()";
                mysql_query($query_reg);
            }
             end by subhash


           // $fullname_encode=urlencode($fullname);
//file_get_contents("https://www.callme4.com:9090/plugins/userService/userservice?type=add&secret=root&username=$userid&password=$userid&name=$fullname_encode&email=$email");
        

           
        }else{
            $registration_status=false;
            $messageerror="Error on Server.";
        }
        return response()->json($data, 200);


    }
*/

    /**
     * To register a user.
     *
     * @return Response
     */
    public function register($phone,$username,$email)
    {

        $genratedVal = $this->gen_card_with_alias();
        
		$activation = $this->gen_activation_code();
   
		//  $phoneNumber = "919717132393";
        $phoneNumber = $phone;
        $omobile = '0091' . $phoneNumber;
        $fdial = '91' . $this->convertFdialkey($omobile);
        // return $requestData;


        // return $genratedVal;
        $userId=$genratedVal['user'];
        $useralias=$genratedVal['useralias'];
        $pass=$genratedVal['pass'];
        $loginkey=$genratedVal['loginkey'];
        $mobile=$phone;
        //  $email='amit110387@gmail.com';
        // $email=$email;

//$userId=3296995172;
       
		$data = \DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $userId)
            ->get();

        
        // return count($data);
        if (count($data) == 0) {
            $data = [
                'creationdate' => date('Y-m-d H:i:s'),
                'expirationdate' => date('Y-m-d H:i:s', (strtotime(date('Y-m-d H:i:s')) + 24 * 3600 * 60)),
                'username' => $userId,
                'useralias' => $useralias,
                'uipass' => $pass,
                'credit' => '5.00',
                'tariff' => '1',
                'activated' => 'f',
                'status' => '1',
                'lastname' => '',
                'firstname' => '',
                'phone' => $mobile,
                'FDial' => $fdial,
                'email' => $email,
                'simultaccess'=>'1',
				'nbused' => '1',
                'invoiceday' => '1',
                'activationCode' => $activation,
                'loginkey' => $loginkey
            ];
     
		    \DB::connection('a2billing')->table('cc_card')
                ->insertGetId($data); 
				
			/* $lastInsertedID = \DB::connection('a2billing')->table('cc_card')->insert( $data )->lastInsertId();
				echo 'dfdsfsdf'; */
				
				$data = \DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=', $mobile)
            ->get();
					
			$id = $data[0]->id;
			 
		}
        else{
            $id =$data[0]->id;

        }
        /************NOW MAKE ENTRY OTHER TABLES*******************/
		
        if($id){
            


            $data = \DB::connection('a2billing')->table('cc_callerid')
                ->where('id_cc_card', '=', $id)
                ->get();
            if(count($data)==0){
                $data = [
                    'cid' => $mobile,
                    'id_cc_card' => $id,
                    'activated' => 't'
                ];
				
                \DB::connection('a2billing')->table('cc_callerid')
                    ->insertGetId($data);
                 

            }






        }
        return ['user_id'=>$userId,"cc_password"=>$pass,"cc_fdial"=>$fdial];


    }

    /**
     * To register a user.
     *
     * @return Response
     */
    public function toCall()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('profile_username', $requestData)
            && array_key_exists('f_dail', $requestData)
            && array_key_exists('called_username', $requestData)
            )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'profile_username' => $requestData['profile_username'],
            'f_dail' => $requestData['f_dail'],
            'called_username' => $requestData['called_username']
        ];
        $rules = [
            'profile_username' => 'required',
            'f_dail' => 'required',
            'called_username' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }

        $matchThese = ['blocked_by' =>$requestData['profile_username'],'blocked_to'=> $requestData['called_username']];

        $list = CM4BlockUser::where($matchThese)->get();

        $status = $list->count();
        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
         return $result;
        }

        $matchThese = ['blocked_by' =>$requestData['called_username'],'blocked_to'=> $requestData['profile_username']];

        $list = CM4BlockUser::where($matchThese)->get();
        $status = $list->count();

        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
            return $result;
        }

        $data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['called_username'])
            ->get(['FDial','id', 'phone']);
       //return $data;
        $cid= $requestData['profile_username'];
        if(count($data)>0){
            $dailNo=$data[0]->phone;
            $fid=$data[0]->FDial;
            $rcvr_uid=$data[0]->id;

            $data = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo
            ];
            $status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($data);

            $data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['profile_username'])
                ->get(['FDial','id', 'phone']);
            if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
          // return $data;

            $sndr_fid=$data[0]->FDial;

            $sndr_dailNo=$data[0]->phone;

            $data = [
                'sndr_fid' =>$sndr_fid,
                'rcvr_uid' => $rcvr_uid
            ];
            $status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')
                ->insert($data);
            if($status && $status2){


                $data =\DB::connection('mysql')->table('cm4_dial_number')
                    ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                    ->where('used_flag', '=', 0)
                    ->take(1)->get();
           //    return $data;
                // get random number from cm4_dial_number

               // $data =\DB::connection('a2billing')->table('cm4_dial_number') ->where('used_flag', '=', 0)
                  //  ->get(['FDial','id', 'phone']);
                if(count($data)!=0) {
                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

                }
                else
                {
                  //  CM4DialNumber::where(true) ->update(['used_flag' => 0]);
                    \DB::table('cm4_dial_number')->update(array('used_flag' => 0));
                    $data =\DB::connection('mysql')->table('cm4_dial_number')
                        ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                        ->where('used_flag', '=', 0)
                        ->take(1)->get();

                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();


                }

                // added by subhash 11 august 2015
                // add pass dial number in cc_dial_detail_pdn

                $data = [
                    'username' =>$cid,
                    'fid' => $fid,
                    'phone1' => $sndr_dailNo,
                    'phone2' => $dailNo,
                    'passdialnum' => $passdialnum,
                ];
                $status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->insert($data);
                $finaldata=['phone_number'=>$passdialnum];
                //$insert="insert into cc_dial_detail_pdn set username='$cid',fid='$fid',phone1='$sndr_dailNo',phone2='$dailNo',passdialnum='$passdialnum'";
                //mysql_query($insert);
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                return $result;
            }

             }else{

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'), "device_key" => $token]);
            return $result;
        }




    }

    
	 /**
     * To Callwithoutccdail.
     *
     * @return Response
     */
    public function toCallwithoutccdail()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('profile_username', $requestData)
            && array_key_exists('f_dail', $requestData)
            && array_key_exists('called_username', $requestData)
            )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'profile_username' => $requestData['profile_username'],
            'f_dail' => $requestData['f_dail'],
            'called_username' => $requestData['called_username']
        ];
        $rules = [
            'profile_username' => 'required',
            'f_dail' => 'required',
            'called_username' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }

        $matchThese = ['blocked_by' =>$requestData['profile_username'],'blocked_to'=> $requestData['called_username']];

        $list = CM4BlockUser::where($matchThese)->get();

        $status = $list->count();
        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
         return $result;
        }

        $matchThese = ['blocked_by' =>$requestData['called_username'],'blocked_to'=> $requestData['profile_username']];

        $list = CM4BlockUser::where($matchThese)->get();
        $status = $list->count();

        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
            return $result;
        }


        $data =CM4UserProfile::where('user_id', '=', $requestData['called_username'])
           ->get(['cc_fdail','cc_password','id','contact_no','email','user_name']);
       //return $data;
        $cid= $requestData['profile_username'];
        if(count($data)>0){
           $dailNo=$data[0]->contact_no;
            $fid=$data[0]->cc_fdail;
            $rcvr_uid=$data[0]->id;
			$cc_password=$data[0]->cc_password;
			$email=$data[0]->email;
			$user_name=$data[0]->user_name;
	$data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['called_username'])->get(['id']);
            if(count($data)==0)
			{	
			$registertocccard=$this->register_to_cccard($requestData['called_username'],$dailNo,$email,$fid,$cc_password,$user_name);
			}
            $data = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo
            ];
            $status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($data);
  $data =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])
                ->get(['cc_fdail','id', 'contact_no']);
            if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
          // return $data;

            $sndr_fid=$data[0]->cc_fdail;

            $sndr_dailNo=$data[0]->contact_no;

            $data = [
                'sndr_fid' =>$sndr_fid,
                'rcvr_uid' => $rcvr_uid
            ];
            $status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')
                ->insert($data);
            if($status && $status2){


                $data =\DB::connection('mysql')->table('cm4_dial_number')
                    ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                    ->where('used_flag', '=', 0)
                    ->take(1)->get();
           //    return $data;
                // get random number from cm4_dial_number

               // $data =\DB::connection('a2billing')->table('cm4_dial_number') ->where('used_flag', '=', 0)
                  //  ->get(['FDial','id', 'phone']);
                if(count($data)!=0) {
                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

                }
                else
                {
                  //  CM4DialNumber::where(true) ->update(['used_flag' => 0]);
                    \DB::table('cm4_dial_number')->where('used_flag', '=',1)->update(array('used_flag' => 0));
                    $data =\DB::connection('mysql')->table('cm4_dial_number')
                        ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                        ->where('used_flag', '=', 0)
                        ->take(1)->get();

                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();


                }

                // added by subhash 11 august 2015
                // add pass dial number in cc_dial_detail_pdn

                $data = [
                    'username' =>$cid,
                    'fid' => $fid,
                    'phone1' => $sndr_dailNo,
                    'phone2' => $dailNo,
                    'passdialnum' => $passdialnum,
                ];
                $status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->insert($data);
                $finaldata=['phone_number'=>$passdialnum];
                //$insert="insert into cc_dial_detail_pdn set username='$cid',fid='$fid',phone1='$sndr_dailNo',phone2='$dailNo',passdialnum='$passdialnum'";
                //mysql_query($insert);
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                return $result;
            }

             }else{

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'), "device_key" => $token]);
            return $result;
        }




    }
	
	
	 //GET CALL COUNT AND UPDATE STATUS 
	public function updatestatus_callcountapi()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		/*$latestversion="151";   
		$latest_version_code='5.0.0.16';	*/
		$latestversion="156";   
		$latest_version_code='6.0.0.0';

	   if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        
		$sndr_dailNo=$requestData['contact_no'];
		$sndr_Uid=$requestData['uid'];
		$callcount=0;
		
			
		$details_url = "http://172.16.200.35:8983/solr/search/select?q=*%3A*&fq=id%3A$sndr_Uid&wt=json&indent=true";
		$details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);
		$response_arr= $response["response"]["docs"];
		
		$updateprofile=0;
		$rateus=0;
		if(count($response_arr)>0)
		{	
		$updateprofile=1;
		$profile_pic=$response_arr[0]['profile_pic'];
		$services=$response_arr[0]['service'];
		$address=$response_arr[0]['service'];
		
		if($response_arr[0]['profile_pic']!="")
		{
		$updateprofile=1;	
		}
		
		}
	
	
	//GET OFFER STATUS 
	$is_offer=0;
	$checkdate="SELECT count(*) as num FROM `cm4_user_offers` where  ((CURDATE() between offer_start_date and offer_end_date) or (CURDATE()<=offer_start_date))  and uid='".$sndr_Uid."' and is_active!='2'";

		//$checkdate="SELECT count(*) as num FROM `cm4_user_offers` where uid='".$sndr_Uid."' and is_active!='2'";
	  
	   $qrychkdate= \ DB::select($checkdate);
		
		if($qrychkdate[0]->num > 0)
		{
	    $is_offer=1;
		}
	
	$finaldata=['updateprofile'=>$updateprofile,'is_offer'=>$is_offer,'latest_version'=>$latestversion,'latest_version_code'=>$latest_version_code,'rate_opt'=>'0,1,2,3,4,5,10,15,20,25,30,35,40,50,60,70,80,90,100,150,200','search_text'=>'Astrology,Relationship Consultants,Poet,SSC Exam Preparation,Popular Youtubers,Teaching,Yoga,Entertainment,Board Exam Preparation'];
	$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
	
	}
	
	
	/**
     * Add play store Rating .
     *
     * @return Response
     */
    public function addplaystorerating() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        
		$sndr_dailNo=$requestData['contact_no'];
		$sndr_Uid=$requestData['uid'];
		$callcount=0;
			$data = [
                "uid" => $sndr_Uid,
                "contact_no" =>$sndr_dailNo,
				];
            CM4AppstoreRating::create($data);
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
		return response()->json($result, 200);
		}

	//Adding Paytm Transaction Details
	/**
     * Add play store Rating .
     *
     * @return Response
     */
    public function addpaytmtransaction() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        
		$uid=$requestData['uid'];
		$contact_no=$requestData['contact_no'];
		$GATEWAYNAME=$requestData['GATEWAYNAME'];
		$PAYMENTMODE=$requestData['PAYMENTMODE'];
		$TXNDATE=$requestData['TXNDATE'];
		$STATUS=$requestData['STATUS'];
		$MID=$requestData['MID'];
		$CURRENCY=$requestData['CURRENCY'];
		$ORDERID=$requestData['ORDERID'];
		$TXNID=$requestData['TXNID'];
		$TXNAMOUNT=$requestData['TXNAMOUNT'];
		$BANKTXNID=$requestData['BANKTXNID'];
		$BANKNAME=$requestData['BANKNAME'];
		$RESPMSG=$requestData['RESPMSG'];
		$RESPCODE=$requestData['RESPCODE'];
		$CHECKSUMHASH=$requestData['CHECKSUMHASH'];
		//Check Paytm Transaction ID
		$chkstatus=$this->PaytmTransactionStatus($ORDERID);
		$txnid=$chkstatus['TXNID'];
		if($chkstatus['TXNAMOUNT']!=$TXNAMOUNT){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
			return response()->json($result, 200);
		}
		$checkdup=CM4TransactionDetails::where('ORDERID',$requestData['ORDERID'])->where('CHECKSUMHASH',$requestData['CHECKSUMHASH'])->where('TXNDATE',$requestData['TXNDATE'])->get(['ORDERID']);

 		$dupcount=$checkdup->count();
		if($dupcount==0 && $txnid!="" && $chkstatus['TXNAMOUNT']!='')
		{
		$data = [
                "uid" => $uid,
                "contact_no" =>$contact_no,
				 "GATEWAYNAME" => $GATEWAYNAME,
                "PAYMENTMODE" =>$PAYMENTMODE,
				 "TXNDATE" => $TXNDATE,
                "STATUS" =>$STATUS,
				 "MID" => $MID,
                "CURRENCY" =>$CURRENCY,
				 "ORDERID" => $ORDERID,
                "TXNID" =>$TXNID,
				 "TXNAMOUNT" => $chkstatus['TXNAMOUNT'],
                "BANKTXNID" =>$BANKTXNID,
				 "BANKNAME" => $BANKNAME,
                "RESPMSG" =>$RESPMSG,
				 "RESPCODE" => $RESPCODE,
                "CHECKSUMHASH" =>$CHECKSUMHASH
				];
		    CM4TransactionDetails::create($data);
		    $newuseramunt=$chkstatus['TXNAMOUNT'];
		 //update to cc_card
			\DB::connection('a2billing')->statement("update cc_card set credit=credit + $newuseramunt where phone='".$contact_no."'");
		}	
		  
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$piggybal=0;
		if(count($CreditInfo)=='1')
		{
		$piggybal=$CreditInfo[0]->piggy_bal;
		}		
			$finaldata=['piggybal'=>$piggybal];
			
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),"data"=>$finaldata,"device_key" => $token]);
		return response()->json($result, 200);
		}
	
	 //Check Paytm Transaction..
	 public function PaytmTransactionStatus($order_id)
{
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");
include(app_path().'/function/config_paytm.php');
include(app_path().'/function/encdec_paytm.php');
$checkSum = "";
$data = array(
"MID"=>"VIRTua95596367428403",
"ORDER_ID"=>$order_id,
);

$key = 'cFU01mdO@P@MXDPj';
$checkSum =getChecksumFromArray($data, $key);

$requestParamList=array('MID'=>'VIRTua95596367428403',"ORDERID"=>$order_id,"CHECKSUMHASH"=>$checkSum);

$apiURL = "https://secure.paytm.in/oltp/HANDLER_INTERNAL/getTxnStatus";

$jsonResponse = "";
	$responseParamList = array();
	$JsonData =json_encode($requestParamList);
	$postData = 'JsonData='.urlencode($JsonData);
	$ch = curl_init($apiURL);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                         
	'Content-Type: application/json', 
	'Content-Length: ' . strlen($postData))                                                                       
	);  
	$jsonResponse = curl_exec($ch);   
	$responseParamList = json_decode($jsonResponse,true);
	
	return $responseParamList;
}

	 
	 
	 /**
     * Freecall Api.
     *
     * @return Response
     */
    public function freecallapi()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('profile_username', $requestData)
            && array_key_exists('f_dail', $requestData)
            && array_key_exists('called_username', $requestData)
            )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'profile_username' => $requestData['profile_username'],
            'f_dail' => $requestData['f_dail'],
            'called_username' => $requestData['called_username']
        ];
        $rules = [
            'profile_username' => 'required',
            'f_dail' => 'required',
            'called_username' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }

        $matchThese = ['blocked_by' =>$requestData['profile_username'],'blocked_to'=> $requestData['called_username']];

        $list = CM4BlockUser::where($matchThese)->get();

        $status = $list->count();
        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
         return $result;
        }

        $matchThese = ['blocked_by' =>$requestData['called_username'],'blocked_to'=> $requestData['profile_username']];

        $list = CM4BlockUser::where($matchThese)->get();
        $status = $list->count();

        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
            return $result;
        }


        $data =CM4UserProfile::where('user_id', '=', $requestData['called_username'])
           ->get(['cc_fdail','cc_password','id','contact_no','email','user_name']);
       //return $data;
        $cid= $requestData['profile_username'];
        if(count($data)>0){
           $dailNo=$data[0]->contact_no;
            $fid=$data[0]->cc_fdail;
            $rcvr_uid=$data[0]->id;
			$cc_password=$data[0]->cc_password;
			$email=$data[0]->email;
			$user_name=$data[0]->user_name;
	$data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['called_username'])->get(['id']);
            if(count($data)==0)
			{	
			$registertocccard=$this->register_to_cccard($requestData['called_username'],$dailNo,$email,$fid,$cc_password,$user_name);
			}
            $data = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo
            ];
            $status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($data);
  $data =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])
                ->get(['cc_fdail','id', 'contact_no']);
            if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
          // return $data;

            $sndr_fid=$data[0]->cc_fdail;

            $sndr_dailNo=$data[0]->contact_no;

            $data = [
                'sndr_fid' =>$sndr_fid,
                'rcvr_uid' => $rcvr_uid
            ];
            $status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')
                ->insert($data);
            if($status && $status2){

				$passdialnum='911206628560';
				$arrayjsondata['phone_number'] = $passdialnum;
               
			
		$phone2active=0;	
		
			$data = [
                    
					'username' =>$cid,
                    'fid' => $fid,
                    'phone1' => $sndr_dailNo,
                    'phone2' => $dailNo,
                    'passdialnum' => $passdialnum,
					'isphone2active'=>$phone2active
                ];
                $status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->insert($data);
                
		
				//New Code added for callback
				 $unquieid=$this->gen_unique_code();
				
				$dialnum=str_pad($sndr_dailNo, 11,"0",STR_PAD_LEFT);
				$dialnum1=str_pad($sndr_dailNo, 10,STR_PAD_LEFT);
			//	$channel="IAX2/a2b_callme4live/$dialnum";
				$channel="Local/$dialnum@outbound/n";
				//$channel="IAX2/a2b_tata/09873851557";	
				 $callback = [
                    'uniqueid'=>$unquieid,
					'status' =>'PENDING',
                    'server_ip' =>'localhost',
                    'channel' => $channel,
                    'exten'=>$passdialnum,
					'priority'=>'1',
					'context'=>'a2billing-callback',
					'id_server' => '1',
                    'id_server_group' =>'1',
					'callerid'=>$dialnum1,
					'account'=>$cid
                ];
                $status2 = \DB::connection('a2billing')->table('cc_callback_spool')
                    ->insert($callback);
				
				
				//To check phone 2 is active or not to control callback functionality..
			$query="select id from CM4_user_info where  phone='".$dailNo."'";
		$isphone2active= \ DB::select($query);
				if(count($isphone2active)>0)
			{
			$phone2active='1';	
			}
			$updateisactive = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->where('phone2', '=',$dailNo)->update(array('isphone2active' =>$phone2active));
				
				$finaldata=['phone_number'=>$passdialnum];
                //$insert="insert into cc_dial_detail_pdn set username='$cid',fid='$fid',phone1='$sndr_dailNo',phone2='$dailNo',passdialnum='$passdialnum'";
                //mysql_query($insert);
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                return $result;
            }

             }else{

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'), "device_key" => $token]);
            return $result;
        }

  }
	
	
 /**
     * cm4call Api.
     *
     * @return Response
     */
    public function cm4callapi()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('profile_username', $requestData)
            && array_key_exists('f_dail', $requestData)
            && array_key_exists('called_username', $requestData)
            )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'profile_username' => $requestData['profile_username'],
            'f_dail' => $requestData['f_dail'],
            'called_username' => $requestData['called_username']
        ];
        $rules = [
            'profile_username' => 'required',
            'f_dail' => 'required',
            'called_username' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
           
        }
	//Same no calling
		 if ($requestData['profile_username']== $requestData['called_username']) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => 'Sorry unable to call on your own number.','errorCode'=>'400','errorDesc'=>"Sorry unable to call on your own number.", "device_key" => $token]);
            return $result;
        }
	
		
        $matchThese = ['blocked_by' =>$requestData['profile_username'],'blocked_to'=> $requestData['called_username']];

        $list = CM4BlockUser::where($matchThese)->get();

        $status = $list->count();
        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
         return $result;
        }

        $matchThese = ['blocked_by' =>$requestData['called_username'],'blocked_to'=> $requestData['profile_username']];

        $list = CM4BlockUser::where($matchThese)->get();
        $status = $list->count();

        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
            return $result;
        }
  $data =CM4UserProfile::where('user_id', '=', $requestData['called_username'])
           ->get(['cc_fdail','cc_password','id','contact_no','email','user_name','per_min_val']);
        
		$cid= $requestData['profile_username'];
        if(count($data)>0){
           
		    $calleduserchares=$data[0]->per_min_val;
		   //CHECK OFFERS
		   $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$data[0]->id."' and (CURDATE() between offer_start_date and offer_end_date)");	
		    
			if(count($selectofferrate)>0)
			{
			$calleduserchares=$selectofferrate[0]->offer_rate;	
			}
		  
		  if($calleduserchares>0)
		   {
			$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE username=$requestData[profile_username]";
			$CreditInfo= \DB::connection('a2billing')->select($qry);
			$ccpiggybal=$CreditInfo[0]->piggy_bal;
			
			
			/* $sufficient_bal =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])->where('piggy_bal', '>=',$calleduserchares)->get(); */
				if($ccpiggybal<$calleduserchares){
		    $result = collect(["status" => "2", "message" => 'Do not have sufficient Balance.','errorCode'=>'104','errorDesc'=>'Do not have sufficient Balance.', "device_key" => $token,"piggy_bal"=>$ccpiggybal]);
                return $result;
			}
		   //To check called user Charges
			else
			{
				if($calleduserchares=='1.00' || $calleduserchares=='1')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'5'));
				}	
			//for 2 rupees
			if($calleduserchares=='2.00' || $calleduserchares=='2')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'6'));
				}
			//for 3 rupees
			if($calleduserchares=='3.00' || $calleduserchares=='3')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'7'));
				}
			//for 4 rupees
			if($calleduserchares=='4.00' || $calleduserchares=='4')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'8'));
				}
			//for 5 rupees
			if($calleduserchares=='5.00' || $calleduserchares=='5')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'9'));
				}
			//for 6 rupees
			if($calleduserchares=='6.00' || $calleduserchares=='6')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'10'));
				}
			//for 7 rupees
			if($calleduserchares=='7.00' || $calleduserchares=='7')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'11'));
				}
			//for 8 rupees
			if($calleduserchares=='8.00' || $calleduserchares=='8')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'12'));
				}
				//for 9 rupees
			if($calleduserchares=='9.00' || $calleduserchares=='9')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'13'));
				}
				//for 10 rupees
			if($calleduserchares=='10.00' || $calleduserchares=='10')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'14'));
				}

				if($calleduserchares=='15.00' || $calleduserchares=='15')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'21'));
				}
				
				//for 20 rupees
			if($calleduserchares=='20.00' || $calleduserchares=='20')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'16'));
				}

				if($calleduserchares=='25.00' || $calleduserchares=='25')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'22'));
				}

				//for 30 rupees
			if($calleduserchares=='30.00' || $calleduserchares=='30')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'17'));
				}

				if($calleduserchares=='35.00' || $calleduserchares=='35')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'23'));
				}

				//for 40 rupees
			if($calleduserchares=='40.00' || $calleduserchares=='40')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'18'));
				}
					//for 50 rupees
			if($calleduserchares=='50.00' || $calleduserchares=='50')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'19'));
				}

				if($calleduserchares=='60.00' || $calleduserchares=='60')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'26'));
				}

				if($calleduserchares=='70.00' || $calleduserchares=='70')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'27'));
				}

				if($calleduserchares=='80.00' || $calleduserchares=='80')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'28'));
				}

				if($calleduserchares=='90.00' || $calleduserchares=='90')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'29'));
				}
			
				//for 100 rupees
			if($calleduserchares=='100.00' || $calleduserchares=='100')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'25'));
				}
			
			//for 150 rupees
			if($calleduserchares=='150.00' || $calleduserchares=='150')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'20'));
				}

				if($calleduserchares=='200.00' || $calleduserchares=='200')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'30'));
				}
			
			}
		   }
		   //If no per min value set for user
		   else
		   {
			$callerusername=$requestData['profile_username'];
			$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'1'));   
		   }
		   
			$dailNo=$data[0]->contact_no;
            $fid=$data[0]->cc_fdail;
            $rcvr_uid=$data[0]->id;
			$cc_password=$data[0]->cc_password;
			$email=$data[0]->email;
			$user_name=$data[0]->user_name;
	$data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['called_username'])->get(['id']);
            if(count($data)==0)
			{	
			$registertocccard=$this->register_to_cccard($requestData['called_username'],$dailNo,$email,$fid,$cc_password,$user_name);
			}
           /*  $data = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo
            ];
         
			$status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($data); */
  $data =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])
                ->get(['cc_fdail','id', 'contact_no']);
			
			
		   if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
          // return $data;
			$sndr_fid=$data[0]->cc_fdail;
			$sndr_dailNo=$data[0]->contact_no;
		$data = [
                'sndr_fid' =>$sndr_fid,
                'rcvr_uid' => $rcvr_uid
            ];
            $status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')
                ->insert($data);
            if($status2){
        $data =\DB::connection('mysql')->table('cm4_dial_number')
                    ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                    ->where('used_flag', '=', 0)
                    ->take(1)->get();
                if(count($data)!=0) {
                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

                }
                else
                {
                    \DB::table('cm4_dial_number')->where('used_flag', '=',1)->update(array('used_flag' => 0));
                    $data =\DB::connection('mysql')->table('cm4_dial_number')
                        ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                        ->where('used_flag', '=',0)
                        ->take(1)->get();

                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

			}
			
		$phone2active=0;	
		
			//Insert into cc_dial_detail
     $cc_dial_detail_array = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo,
               'caller_phone' =>$sndr_dailNo,
               'ext' => $passdialnum
         ];
         $status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($cc_dial_detail_array);
    //End Insert into cc_dial_detail
			
			
			$data = [
                    
					'username' =>$cid,
                    'fid' => $fid,
                    'phone1' => $sndr_dailNo,
                    'phone2' => $dailNo,
                    'passdialnum' => $passdialnum,
					'isphone2active'=>$phone2active
                ];
                $status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->insert($data);
               
				//To check phone 2 is active or not to control callback functionality..
			$query="select id from CM4_user_info where  phone='".$dailNo."'";
		$isphone2active= \ DB::select($query);
				if(count($isphone2active)>0)
			{
			$phone2active='1';	
			}
			$updateisactive = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->where('phone2', '=',$dailNo)->update(array('isphone2active' =>$phone2active));
				$finaldata=['phone_number'=>$passdialnum];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                return $result;
            }
			}else{
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'), "device_key" => $token]);
            return $result;
        }
	}
	


	 /**
     * cm4call Api.
     * Update on 26/06/2018
     * @return Response
     */
	 
    public function cm4callapi_ctry_code()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('profile_username', $requestData)
            && array_key_exists('f_dail', $requestData)
            && array_key_exists('called_username', $requestData)
            )) {
           $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'profile_username' => $requestData['profile_username'],
            'f_dail' => $requestData['f_dail'],
            'called_username' => $requestData['called_username']
        ];
        $rules = [
            'profile_username' => 'required',
            'f_dail' => 'required',
            'called_username' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
           
        }
	//Same no calling
		 if ($requestData['profile_username']== $requestData['called_username']) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => 'Sorry unable to call on your own number.','errorCode'=>'400','errorDesc'=>"Sorry unable to call on your own number.", "device_key" => $token]);
            return $result;
        }

        if ($requestData['called_username']== '2602249508') {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => 'Sorry unable to call on your own number.','errorCode'=>'400','errorDesc'=>"Sorry unable to call on your own number.", "device_key" => $token]);
            return $result;
        }
	
		
        $matchThese = ['blocked_by' =>$requestData['profile_username'],'blocked_to'=> $requestData['called_username']];

        $list = CM4BlockUser::where($matchThese)->get();

        $status = $list->count();
        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
         return $result;
        }

        $matchThese = ['blocked_by' =>$requestData['called_username'],'blocked_to'=> $requestData['profile_username']];

        $list = CM4BlockUser::where($matchThese)->get();
        $status = $list->count();

        if($status!=0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
            return $result;
        }
  $data =CM4UserProfile::where('user_id', '=', $requestData['called_username'])
           ->get(['cc_fdail','cc_password','id','contact_no','email','user_name','per_min_val','c_code']);
        
		$cid= $requestData['profile_username'];
        if(count($data)>0){
           
		    $calleduserchares=$data[0]->per_min_val;
		   //CHECK OFFERS
		   $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$data[0]->id."' and (CURDATE() between offer_start_date and offer_end_date)");	
		    
			if(count($selectofferrate)>0)
			{
			$calleduserchares=$selectofferrate[0]->offer_rate;	
			}
		  
		  if($calleduserchares>0)
		   {
			$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE username=$requestData[profile_username]";
			$CreditInfo= \DB::connection('a2billing')->select($qry);
			$ccpiggybal=$CreditInfo[0]->piggy_bal;
			
			
			/* $sufficient_bal =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])->where('piggy_bal', '>=',$calleduserchares)->get(); */
				if($ccpiggybal<$calleduserchares){
		    $result = collect(["status" => "2", "message" => 'Do not have sufficient Balance.','errorCode'=>'104','errorDesc'=>'Do not have sufficient Balance.', "device_key" => $token,"piggy_bal"=>$ccpiggybal]);
                return $result;
			}
		   //To check called user Charges
			else
			{
				if($calleduserchares=='1.00' || $calleduserchares=='1')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'5'));
				}	
			//for 2 rupees
			if($calleduserchares=='2.00' || $calleduserchares=='2')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'6'));
				}
			//for 3 rupees
			if($calleduserchares=='3.00' || $calleduserchares=='3')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'7'));
				}
			//for 4 rupees
			if($calleduserchares=='4.00' || $calleduserchares=='4')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'8'));
				}
			//for 5 rupees
			if($calleduserchares=='5.00' || $calleduserchares=='5')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'9'));
				}
			//for 6 rupees
			if($calleduserchares=='6.00' || $calleduserchares=='6')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'10'));
				}
			//for 7 rupees
			if($calleduserchares=='7.00' || $calleduserchares=='7')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'11'));
				}
			//for 8 rupees
			if($calleduserchares=='8.00' || $calleduserchares=='8')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'12'));
				}
				//for 9 rupees
			if($calleduserchares=='9.00' || $calleduserchares=='9')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'13'));
				}
				//for 10 rupees
			if($calleduserchares=='10.00' || $calleduserchares=='10')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'14'));
				}

				if($calleduserchares=='15.00' || $calleduserchares=='15')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'21'));
				}
				
				//for 20 rupees
			if($calleduserchares=='20.00' || $calleduserchares=='20')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'16'));
				}

				if($calleduserchares=='25.00' || $calleduserchares=='25')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'22'));
				}

				//for 30 rupees
			if($calleduserchares=='30.00' || $calleduserchares=='30')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'17'));
				}

				if($calleduserchares=='35.00' || $calleduserchares=='35')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'23'));
				}

				//for 40 rupees
			if($calleduserchares=='40.00' || $calleduserchares=='40')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'18'));
				}
					//for 50 rupees
			if($calleduserchares=='50.00' || $calleduserchares=='50')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'19'));
				}

				if($calleduserchares=='60.00' || $calleduserchares=='60')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'26'));
				}

				if($calleduserchares=='70.00' || $calleduserchares=='70')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'27'));
				}

				if($calleduserchares=='80.00' || $calleduserchares=='80')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'28'));
				}

				if($calleduserchares=='90.00' || $calleduserchares=='90')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'29'));
				}
			
				//for 100 rupees
			if($calleduserchares=='100.00' || $calleduserchares=='100')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'25'));
				}
			
			//for 150 rupees
			if($calleduserchares=='150.00' || $calleduserchares=='150')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'20'));
				}

				if($calleduserchares=='200.00' || $calleduserchares=='200')
				{
				$callerusername=$requestData['profile_username'];
				$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'30'));
				}
			
			}
		   }
		   //If no per min value set for user
		   else
		   {
			$callerusername=$requestData['profile_username'];
			$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'1'));   
		   }
		 	$c_code=$data[0]->c_code;
			$dailNo=$data[0]->contact_no;
            $fid=$data[0]->cc_fdail;
            $rcvr_uid=$data[0]->id;
			$cc_password=$data[0]->cc_password;
			$email=$data[0]->email;
			$user_name=$data[0]->user_name;
	$data =\DB::connection('a2billing')->table('cc_card') ->where('username', '=', $requestData['called_username'])->get(['id']);
            if(count($data)==0)
			{	
			$registertocccard=$this->register_to_cccard($requestData['called_username'],$dailNo,$email,$fid,$cc_password,$user_name);
			}
           /*  $data = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo
            ];
         
			$status = \DB::connection('a2billing')->table('cc_dial_detail')
                ->insertGetId($data); */
  $data =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])
                ->get(['cc_fdail','id', 'contact_no']);
			
			
		   if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
          // return $data;
			$sndr_fid=$data[0]->cc_fdail;
			$sndr_dailNo=$data[0]->contact_no;
		$data = [
                'sndr_fid' =>$sndr_fid,
                'rcvr_uid' => $rcvr_uid
            ];
            $status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')
                ->insert($data);
            if($status2){
        $data =\DB::connection('mysql')->table('cm4_dial_number')
                    ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                    ->where('used_flag', '=', 0)
                    ->take(1)->get();
                if(count($data)!=0) {
                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

                }
                else
                {
                    \DB::table('cm4_dial_number')->where('used_flag', '=',1)->update(array('used_flag' => 0));
                    $data =\DB::connection('mysql')->table('cm4_dial_number')
                        ->select('number', \DB::raw('id,concat(prefix,number) as number'))
                        ->where('used_flag', '=',0)
                        ->take(1)->get();

                    $passdialnum = $data[0]->number;
                    $arrayjsondata['phone_number'] = $passdialnum;
                    $number_id = $data[0]->id;

                    $user = CM4DialNumber::find($number_id);
                    $user->used_flag = 1;
                    $user->update_count += 1;
                    $user->save();

			}
			
		$phone2active=0;	
		
			//Insert into cc_dial_detail
     $cc_dial_detail_array = [
                'cid' =>$cid,
                'fid' => $fid,
                'phone' => $dailNo,
               'caller_phone' =>$sndr_dailNo,
               'ext' => $passdialnum
         ];
        
		
		 $status = \DB::connection('a2billing')->table('cc_dial_detail')
               ->insertGetId($cc_dial_detail_array);
    //End Insert into cc_dial_detail
			
			
			$data = [
                    
					'username' =>$cid,
                    'fid' => $fid,
                    'phone1' => $sndr_dailNo,
                    'phone2' =>$dailNo,
                    'passdialnum' => $passdialnum,
					'isphone2active'=>$phone2active
                ];
                //print_r($data);die;
				
				$status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')->insert($data);
               
				//To check phone 2 is active or not to control callback functionality..
			$query="select id from CM4_user_info where  phone='".$dailNo."'";
		$isphone2active= \ DB::select($query);
				if(count($isphone2active)>0)
			{
			$phone2active='1';	
			}
			$updateisactive = \DB::connection('a2billing')->table('cc_dial_detail_pdn')
                    ->where('phone2', '=',$dailNo)->update(array('isphone2active' =>$phone2active));
				$finaldata=['phone_number'=>$passdialnum];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finaldata, "device_key" => $token]);
                return $result;
            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                return $result;
            }
			}else{
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'), "device_key" => $token]);
            return $result;
        }
	}
	
//Get unique ID for callback Spool TABLES******************
	 public function gen_unique_code($table = "cc_callback_spool") {
       $ctr=0;

        $card_gen = "";
        $flag = true;
        while ($flag) {
            $ctr++;
            $card_gen = $this->MDP_STRING(8);


            $data =\DB::connection('a2billing')->table('cc_callback_spool')
                ->where('uniqueid', '=', $card_gen)
                ->get();
            if (count($data) > 0)
                continue;

            if ($ctr == 1000)
                return false;
            $flag = false;
        }

        return ($card_gen) ? $card_gen : false;
    }
	
	
			//*******WORKING******************
	 public function gen_referal_code() {
       $ctr=0;

        $card_gen = "";
        $flag = true;
        while ($flag) {
            $ctr++;
            $card_gen = $this->MDP_STRING(8);
			$data =CM4UserRefer::where('refer_code', '=', $card_gen)->get(['refer_code']);
            if (count($data) > 0)
                continue;

            if ($ctr == 1000)
                return false;
            $flag = false;
        }

        return ($card_gen) ? $card_gen : false;
    }
	
	
	// REGISTER TO CC_CALL
	 /**
     * To register a user.
     *
     * @return Response
     */
    public function register_to_cccard($username,$phone,$email,$fid,$password,$user_name)
    {

        $genratedVal = $this->gen_card_with_alias();
        
		$activation = $this->gen_activation_code();
        $phoneNumber = $phone;
        $fdial = $fid;
        $userId=$genratedVal['user'];
        $useralias=$genratedVal['useralias'];
        $pass=$genratedVal['pass'];
        $loginkey=$genratedVal['loginkey'];
        $mobile=$phone;
       
        $data = \DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=',$phone)
            ->get();

        if (count($data) == 0) {
            $data = [
                'creationdate' => date('Y-m-d H:i:s'),
                'expirationdate' => date('Y-m-d H:i:s', (strtotime(date('Y-m-d H:i:s')) + 24 * 3600 * 60)),
                'username' => $username,
                'useralias' => $useralias,
                'uipass' => $password,
                'credit' => '0.0',
                'tariff' => '1',
                'activated' => 'f',
                'status' => '1',
                'lastname' => '',
                'firstname' => $user_name,
                'phone' => $mobile,
                'FDial' => $fdial,
                'email' => $email,
                'nbused' => '1',
                'invoiceday' => '1',
                'activationCode' => $activation,
                'loginkey' => $loginkey
            ];
     
		    \DB::connection('a2billing')->table('cc_card')
                ->insertGetId($data); 
			
				$data = \DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=', $mobile)
            ->get();
					
			$id = $data[0]->id;
			 
		}
        else{
            $id =$data[0]->id;

        }
        /************NOW MAKE ENTRY OTHER TABLES*******************/
		
        if($id){
            


            $data = \DB::connection('a2billing')->table('cc_callerid')
                ->where('id_cc_card', '=', $id)
                ->get();
            if(count($data)==0){
                $data = [
                    'cid' => $mobile,
                    'id_cc_card' => $id,
                    'activated' => 't'
                ];
				
                \DB::connection('a2billing')->table('cc_callerid')
                    ->insertGetId($data);
            }

        }
        return ['user_id'=>$userId,"cc_password"=>$pass,"cc_fdial"=>$fdial];


    }
	
	
	
	/**
     * To get code.
     *
     * @return Response
     */
    public function getcode_bak() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('country_code', $requestData)
            && array_key_exists('phone', $requestData)
            && array_key_exists('device_id', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
           // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
          //  $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'country_code' => $requestData['country_code'],
            'phone' => $requestData['phone'],
            'device_id' => $requestData['device_id']
        ];
        $rules = [
            'country_code' => 'required|numeric',
            'phone' => 'required|phone',
            'device_id' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }

        $matchThese = ['contact_no' => $requestData['phone']];

        $user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();

        if($status!=0){
            //return $user[0]->device_id;
            if($user[0]->device_id==$requestData['device_id']){
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $user, "device_key" => $token]);
               // $result = collect(["status_code" => "100", "message" => \Config::get('constants.results.100'), "data" => $user, "device_key" => $token]);
                return $result;

            }
			else{
				$phoneNumber = $requestData['country_code'].$requestData['phone'];
                        $code=$this->rand_string(4);
                        $this->sendsms($phoneNumber, $code);

                        $data = array("phone" => $requestData['phone'],
                            "c_code" => $requestData['country_code'],
                            "device_id" => $requestData['device_id'],
                            "code" => $code,
                            "status" => 0
                        );

                        CM4UserInfo::create($data);

                $data=['sms_code'=>$code];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>\Config::get('constants.results.116'), "data" => $data, "device_key" => $token]);



                      //  $result = collect(["status_code" => "116", "message" => \Config::get('constants.results.116'), "data" => $code, "device_key" => $token]);
                    }
		}
			else{


                $phoneNumber = $requestData['country_code'].$requestData['phone'];

                $matchThese = ['phone' => $requestData['phone']];
                $user = CM4UserInfo::where($matchThese)->get();
                $userCount=$user->count();
                if ($userCount == 0) {
                    $code=$this->rand_string(4);
                    $this->sendsms($phoneNumber, $code);
                    $data = array("phone" => $requestData['phone'],
                        "c_code" => $requestData['country_code'],
                        "device_id" => $requestData['device_id'],
                        "code" => $code,
                        "status" => 0
                    );

                    CM4UserInfo::create($data);
                    $data=["sms_code"=>$code,"sms_code"=>$code];
                 //   $result = collect(["status_code" => "100", "message" => \Config::get('constants.results.100'), "data" => $code, "device_key" => $token]);
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);

                } else {
                    $matchThese = ['phone' => $requestData['phone'],'device_id'=>$requestData['device_id']];
                    $user = CM4UserInfo::where($matchThese)->get();
                    $userCount=$user->count();
                    if($userCount!=0) {

                       // $result = collect(["status_code" => "103", "message" => \Config::get('constants.results.103'), "device_key" => $token]);
                        $result = collect(["status" => "0", "message" => \Config::get('constants.results.103'),'errorCode'=>'103','errorDesc'=>\Config::get('constants.results.103'), "device_key" => $token]);
                    }else{
                        $code=$this->rand_string(4);
                        $this->sendsms($phoneNumber, $code);

                        $data = array("phone" => $requestData['phone'],
                            "c_code" => $requestData['country_code'],
                            "device_id" => $requestData['device_id'],
                            "code" => $code,
                            "status" => 0
                        );

                        CM4UserInfo::create($data);
                        $data=['sms_code'=>$code];
                        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>\Config::get('constants.results.116'), "data" => $data, "device_key" => $token]);
                       // $result = collect(["status_code" => "116", "message" => \Config::get('constants.results.116'), "data" => $code, "device_key" => $token]);
                    }

                }


            }


        return response()->json($result, 200);


    }

	//Old code for getcode   
   public function getcodeold() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('country_code', $requestData)
            && array_key_exists('phone', $requestData)
            && array_key_exists('device_id', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 3) {
            //  $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'country_code' => $requestData['country_code'],
            'phone' => $requestData['phone'],
            'device_id' => $requestData['device_id']
        ];
        $rules = [
            'country_code' => 'required|numeric',
            'phone' => 'required|phone',
            'device_id' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
		 
		 
		 $latitude=0;
		 $longitude=0;
		 $city="";
		 $state="";
		 $city1="";
		 $state1="";
		 
		 if(array_key_exists("latitude",$requestData) && array_key_exists("longitude",$requestData) &&array_key_exists("device_imei",$requestData) && array_key_exists("device_android_id",$requestData))
		{
		$latitude=$requestData['latitude'];
		$longitude=$requestData['longitude'];
		$city=$requestData['device_imei'];
		$state=$requestData['device_android_id'];
		$city1=$requestData['city'];
		$state1=$requestData['state'];
		}
        $phoneNumber = $requestData['country_code'].$requestData['phone'];
$checkdup=CM4UserInfo::where('phone','!=',$requestData['phone'])->where('city',$requestData['device_imei'])->get(['phone']);

 $dupcount=$checkdup->count();
		
		if($dupcount==0)
		{
        $matchThese = ['phone' => $requestData['phone'],'device_id'=>$requestData['device_id']];
        $user = CM4UserInfo::where($matchThese)->get();
        $userCount=$user->count();
        if ($userCount == 0) {
            $code=$this->rand_string(4);
            $this->sendsms($phoneNumber, $code);
            $data = array("phone" => $requestData['phone'],
                "c_code" => $requestData['country_code'],
                "device_id" => $requestData['device_id'],
                "code" => $code,
                "status" => 0,
				"latitude"=>$latitude,
				"longitude"=>$longitude,
				"city"=>$city,
				"state"=>$state,
				"city1"=>$city1,
				"state1"=>$state1
			 );
		CM4UserInfo::create($data);

        } else {
            $code=$this->rand_string(4);
            $this->sendsms($phoneNumber, $code);
            $current_rec = CM4UserInfo::find($user[0]['id']);
            $current_rec->code = $code;
            $current_rec->save();

		}

		// $data=["sms_code"=>$code,"sms_code"=>$code];
		$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
              
	   //   $result = collect(["status_code" => "100", "message" => \Config::get('constants.results.100'), "data" => $code, "device_key" => $token]);
        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);
		
		}
		else
		{
		$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
              
	   //   $result = collect(["status_code" => "100", "message" => \Config::get('constants.results.100'), "data" => $code, "device_key" => $token]);
        $result = collect(["status" => "0", "message" =>'Sorry this Device is already exist with another No.','errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);	
		}

  return response()->json($result, 200);


    }

    
	
	  //New Code for Get Code
	  public function getcode() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('country_code', $requestData)
            && array_key_exists('phone', $requestData)
            && array_key_exists('device_id', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
           
            return $result;
        }
       
        $fields = [
            'country_code' => $requestData['country_code'],
            'phone' => $requestData['phone'],
            'device_id' => $requestData['device_id']
        ];
        $rules = [
            'country_code' => 'required|numeric',
            'phone' => 'required|phone',
            'device_id' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
		 
		 
		 $latitude=0;
		 $longitude=0;
		 $city="";
		 $state="";
		 $city1="";
		 $state1="";
		 $msg="";
		 
		 if(array_key_exists("latitude",$requestData) && array_key_exists("longitude",$requestData) &&array_key_exists("device_imei",$requestData) && array_key_exists("device_android_id",$requestData))
		{
		$latitude=$requestData['latitude'];
		$longitude=$requestData['longitude'];
		$city=$requestData['device_imei'];
		$state=$requestData['device_android_id'];
		
		$city1_len=strlen($requestData['city']);
		if($city1_len>200){
			$city1='';
		}else{
			$city1=$requestData['city'];
		}
		$state1=$requestData['state'];
		}
    $phoneNumber = $requestData['country_code'].$requestData['phone'];
	$matchThese = ['phone' => $requestData['phone']];
	$userinfo = CM4UserInfo::where($matchThese)->get(['id','phone','device_id','city']);
	 $userCount=$userinfo->count();
	if($userCount==0)
		{
	//$checkdup=CM4UserInfo::where('city',$requestData['device_imei'])->get(['phone']);
	//$dupcount=$checkdup->count();
	 $mobile='91'.$requestData['phone'];	
	 $code=$this->rand_string(4);
            $this->sendsms($phoneNumber, $code);
            $data = array("phone" =>$mobile,
                "c_code" => $requestData['country_code'],
                "device_id" => $requestData['device_id'],
                "code" => $code,
                "status" => 0,
				"latitude"=>$latitude,
				"longitude"=>$longitude,
				"city"=>$city,
				"state"=>$state,
				"city1"=>$city1,
				"state1"=>$state1
			 );
		CM4UserInfo::create($data);
		$msg="User is Created Successfully.";	
		$status='1';
	
	
	/* 	if($dupcount==0)
		{
        
            $code=$this->rand_string(4);
            $this->sendsms($phoneNumber, $code);
            $data = array("phone" => $requestData['phone'],
                "c_code" => $requestData['country_code'],
                "device_id" => $requestData['device_id'],
                "code" => $code,
                "status" => 0,
				"latitude"=>$latitude,
				"longitude"=>$longitude,
				"city"=>$city,
				"state"=>$state,
				"city1"=>$city1,
				"state1"=>$state1
			 );
		CM4UserInfo::create($data);
		$msg="User is Created Successfully.";	
		$status='1';
		} 
		else
		{
		$status='0';
		$msg="Sorry this Device is already exist with another No.";	
		} */
	}
       else
	   {
		if(trim($requestData['device_id'])==trim($userinfo[0]->device_id))
		{
		$code=$this->rand_string(4);
            $contact_no=$requestData['phone'];
			if($contact_no=='9999120228')
			{
			$code='0007';	
			}
			$this->sendsms($phoneNumber, $code);
            $current_rec = CM4UserInfo::find($userinfo[0]['id']);
            $current_rec->code = $code;
            $current_rec->save();
			$status='1';
		$msg="Your code has been Updated.";		
		}
		else
		{
			$code=$this->rand_string(4);
            $contact_no=$requestData['country_code'].$requestData['phone'];
			if($contact_no=='9999120228')
			{
			$code='0007';	
			}
			$this->sendsms($phoneNumber, $code);
            $data = array("phone" => $contact_no,
                "c_code" => $requestData['country_code'],
                "device_id" => $requestData['device_id'],
                "code" => $code,
                "status" => 0,
				"latitude"=>$latitude,
				"longitude"=>$longitude,
				"city"=>$city,
				"state"=>$state,
				"city1"=>$city1,
				"state1"=>$state1
			 );
			CM4UserInfo::create($data);
		$status='1';
		$msg="Your code has been sent to your Device.";		
		}		
	 }
	   if($status=='1')
	   {
		$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);
	   }
	   else
	   {
        $data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
		$result = collect(["status" => "0", "message" =>$msg,'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);   
	   }
        return response()->json($result, 200);
    }
	
	
	
	//Get code to Register Another Number
	public function getanothercode(){ 
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
		    $requestData = Request::json()->all();
		}else{
		    $requestData = Request::all();
		}
		if (!(array_key_exists('phone', $requestData) && array_key_exists('country_code', $requestData) )) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		}
		$fields = [
			'phone' => $requestData['phone'],
			'country_code' => $requestData['country_code']
		];
		$rules = [
			'phone' => 'required|phone',
			'country_code' => 'required|numeric'
		];
		$valid = \Validator::make($fields, $rules);
		if ($valid->fails()) {
		    return [
		        'status'=>'0',
		        'message' => 'validation_failed',
		        'errorCode'=>'',
		        'errorDesc' => $valid->errors()
		    ];
		}
		$phoneNumber = $requestData['country_code'].$requestData['phone'];
		$mobileno = '91'.$requestData['phone'];
		$matchThese = ['phone' => $mobileno];
		$userinfo = CM4UserInfo::where($matchThese)->get(['id','phone','device_id','city']);
		$userCount=$userinfo->count();

		if($userCount==0){
			$code=$this->rand_string(4);
		    $this->sendsms($phoneNumber, $code);
			$data = array("phone" => $mobileno,
				"c_code" => $requestData['country_code'],
				"code" => $code,
				"device_id"=>$requestData['device_id'],
				"latitude"=>'',
				"longitude"=>'',
				"city"=>'',
				"state"=>'',
				"city1"=>'',
				"state1"=>'',
				"status" => 0);
			CM4UserInfo::create($data);
			$msg="User is Created Successfully.";
			$status='1';
		}else{
			$status='0';
		}

		if($status=='1'){
			$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
			$result = collect(["status" => "1", "message" =>$msg,'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);
		}else{
			$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
		    $result = collect(["status" => "0", "message" =>'Phone number already exist another callme4 account','errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);
		}
		return response()->json($result, 200);
	}
	
	
		//Verify Another Code
		public function verify_anothercode(){
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}

		if(!(array_key_exists('phone', $requestData) && array_key_exists('code', $requestData) && array_key_exists('anotherno', $requestData) && array_key_exists('country_code', $requestData) && array_key_exists('simno', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		}
		if (count($requestData) != 5) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		}
		$fields = [
			'phone' => $requestData['phone'],
			'anotherno' => $requestData['anotherno'],
			'code' => $requestData['code'],
			'country_code' => $requestData['country_code']
		];
		$rules = [
			'phone' => 'required',
			'anotherno' => 'required',
			'code' => 'required|numeric|digits:4',
			'country_code' => 'required',
		];
		$valid = \Validator::make($fields, $rules);
		if ($valid->fails()) {
			return [
				'status'=>'0',
				'message' => 'validation_failed',
				'errorCode'=>'',
				'errorDesc' => $valid->errors()
			];
		}
		$lengthanother=strlen($requestData['anotherno']);
		if($lengthanother==12){
			$mobileno=$requestData['anotherno'];
		}else{
			$mobileno='91'.$requestData['anotherno'];
		}
		

		$matchThese = ['phone' => $mobileno,'code'=>$requestData['code']];
		$user = CM4UserInfo::where($matchThese)->get(['id']);
		$status = $user->count();
		if($status != 0){
			$user = CM4UserInfo::find($user[0]['id']);
			$user->status = 1;
			$user->save();
			$length=strlen($requestData['phone']);
			if($length==12){
				$firstnumber=$requestData['phone'];
			}else{
				$firstnumber='91'.$requestData['phone'];
			}
			$matchThese_1 = ['contact_no' => $firstnumber];
		    $user_pro_data = CM4UserProfile::where($matchThese_1)->get(['id']);

			$user_pro = CM4UserProfile::find($user_pro_data[0]->id);
			$user_pro->marital_status = $mobileno;
			$user_pro->save();

			//--------------insert sim npo---------------------------//
			$matchThese_2 = ['contact_no' => $firstnumber];
		    $user_pro_data2 = CM4UserSimno::where($matchThese_2)->get(['id']);
		    $status_sim = $user_pro_data2->count(); 
		    if($status_sim == 0){ 
		    	$data = array("userid" => $user_pro_data[0]->id,
				"contact_no" => $firstnumber,
				"sim_number" => $requestData['simno']);
				CM4UserSimno::create($data);
		    }else{ 
		    	$user_pro1 = CM4UserSimno::find($user_pro_data2[0]->id);
				$user_pro1->sim_number = $requestData['simno']; 
				$user_pro1->save(); //exit();
		    }

			//-----------------end -----------------------------------//

			$data_card = \DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=', $firstnumber)
            ->get();

            $data_par=array('cid'=>$mobileno,'id_cc_card'=>$data_card[0]->id);
            \DB::connection('a2billing')->table('cc_callerid')
                ->insertGetId($data_par);

			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"device_key" => $token]);
		}else{
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
		}
		 return response()->json($result, 200);
	}
	
	
	/**
     * To send sms to user.
     *
     * @return Response
     */
    public function sendsms($phoneNumber , $txt){

  		$ch = curl_init();
        $receipientno=$phoneNumber;
        $senderID="CALLME";
        $msgtxt="Please enter $txt on the verification field to activate your account";
        $url ="https://2factor.in/API/V1/007bb235-5da4-11e8-a895-0200cd936042/SMS/$receipientno/$txt/cm4new";
        $getresponse=file_get_contents($url);
        return true;

        /*$ch = curl_init();
        $user="eshan@virtualemployee.com:v1rtual";
        $receipientno=$phoneNumber;
        $senderID="CALLME";
        $msgtxt="Please enter $txt on the verification field to activate your account";
        curl_setopt($ch,CURLOPT_URL,  "http://api.mVaayoo.com/mvaayooapi/MessageCompose");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&senderID=$senderID&receipientno=$receipientno&msgtxt=$msgtxt");
        $buffer = curl_exec($ch);
        if(empty ($buffer))
        { echo " buffer is empty ";
            }
        else
        { //echo $buffer;
            }
        curl_close($ch);*/





    }

    
	  /**
     * To send sms to user.
     *
     * @return Response
     */
    public function sendsmsnew($phoneNumber,$txt){

		//Working
        $ch = curl_init();
      
        $receipientno=$phoneNumber;
        $senderID="CALLME";
        $msgtxt="Please enter $txt on the verification field to activate your account";
		curl_setopt($ch,CURLOPT_URL,  "http://trans.kapsystem.com/api/v3/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "workingkey=A2180610a90fe27b89ff4bd901d23bd1d&to=$receipientno&sender=KAPSMS&message=$msgtxt");
		curl_setopt($ch, CURLOPT_POSTFIELDS, "method=sms&api_key=Aac053f88fc9bb3eeb5e2c9f741cb5abc&to=$receipientno&sender=CALLME&message=$msgtxt");
        $buffer = curl_exec($ch);
        if(empty ($buffer))
        { echo " buffer is empty ";
            }
        else
        { //echo $buffer;
            }
        curl_close($ch);
    }

	
	/**
     * To generate the code.
     *
     * @return Response
     */
    public function rand_string( $length ){
        $chars = "0123456789";
        $str="";
        $size = strlen( $chars );
        for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }

        return $str;
    }

    /**
     * Verify SMS.
     *
     * @return Response
     */
    public function verifySms_bak() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $createUser = Request::json()->all();
        } else {

            $createUser = Request::all();
        }

        if (!(array_key_exists('phone', $createUser)
            && array_key_exists('code', $createUser)
            && array_key_exists('device_id', $createUser)
        )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($createUser) != 3) {
           // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $createUser['phone'],
            'code' => $createUser['code'],
            'device_id' => $createUser['device_id']
        ];
        $rules = [
            'phone' => 'required',
            'code' => 'required|numeric|digits:4',
            'device_id' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }

        $matchThese = ['phone' => $createUser['phone'],'device_id'=>$createUser['device_id'],'code'=>$createUser['code']];

        $user = CM4UserInfo::where($matchThese)->get();
        $status = $user->count();
        //return $status;
        if ($status != 0) {

               // $users = CM4UserInfo::where('phone', $createUser['uid'])->get();

                $user = CM4UserInfo::find($user[0]['id']);
                $user->status = 1;
                $user->save();

            $matchThese = ['contact_no' => $user->phone];

            $user = CM4UserProfile::where($matchThese)->get();
            $status = $user->count();
            if($status==0){
                $data =[
                    'user_id'=>'',
                    'user_name'=>'',
                    'profile_pic'=>'',
                    'gender'=>'',
                    'locality'=>'',
                    'age'=>'',
                    'address'=>'',
                    'country'=>'',
                    'city'=>'',
                    'state'=>'',
                    'lat'=>'',
                    'long'=>'',
                    'call_time'=>'',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>'',
                    'contact_no'=>$matchThese['contact_no'],
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>$createUser['device_id'],
                    'cc_password'=>'',
                    'email'=>'',
                    'cc_fdail'=>'',
                    'category'=>'',
                    'data_source'=>''
                ];
                CM4UserProfile::create($data);
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
              // $result = collect(["status" => [ "code" => "112", "message" => \Config::get('constants.results.112')], "device_key" => $token]);
            }else{

                if($createUser['device_id']==$user[0]->device_id){

                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
                  //  $result = collect(["status" => [ "code" => "111", "message" => \Config::get('constants.results.111')], "device_key" => $token]);

                }else{

                    $user = CM4UserProfile::find($user[0]->id);
                    $user->device_id = $createUser['device_id'];
                    $user->save();
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
                    //$result = collect(["status" => [ "code" => "100", "message" => \Config::get('constants.results.100')], "device_key" => $token]);


            }
            }


                //$result=array("success"=>1 ,'message'=>"Verified");
            } else {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
              //  $result = collect(["status" => [ "code" => "113", "message" => \Config::get('constants.results.113')],
                  //  "device_key" => $token]);
                //$result=array("success"=>1 ,'message'=>"Not Verified");
            }

        return response()->json($result, 200);
    }

    
	//Verify SMS Updated
	public function verifySms() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
		
		
		} else {

            $requestData = Request::all();
        }
	
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('code', $requestData)
            && array_key_exists('device_id', $requestData)
            && array_key_exists('country_code', $requestData)
        )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 4) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $requestData['phone'],
            'code' => $requestData['code'],
            'device_id' => $requestData['device_id'],
            'country_code' => $requestData['country_code']
        ];
        $rules = [
            'phone' => 'required',
            'code' => 'required|numeric|digits:4',
            'device_id' => 'required',
            'country_code' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            
        }
		//change
		$mobile= '91'.$requestData['phone'];
		 //Check User Details Status
		$matchThese = ['contact_no' => $mobile];
		$userdetails = CM4UsersDetails::where($matchThese)->get(['id']);
        $userdetailsstatus = $userdetails->count();
		
        $matchThese = ['phone' => $mobile,'device_id'=>$requestData['device_id'],'code'=>$requestData['code']];

        $user = CM4UserInfo::where($matchThese)->get(['id']);
        $status = $user->count();

        //-------sim detail-----------------------------------//
            
      
		if ($status != 0) {

         $user = CM4UserInfo::find($user[0]['id']);
     
			$user->status = 1;
            $user->save();

            $matchThese = ['contact_no' => $user->phone];
            $user = CM4UserProfile::where($matchThese)->get();

            
         
		$status = $user->count();
			
		 $phone= $mobile;
            $email="";
            $name="";
            if($status==0){
				$referal=$this->gen_referal_code();
                $genData= $this->register($phone,$email,$name);
				//return $genData;	
                $userId=$genData['user_id'];
                $fdial=$genData['cc_fdial'];
                $pass=$genData['cc_password'];
				$piggybal="5.00";   
	$category_json="a:0:{}";
	$userinfo=array('user_id'=>$userId,'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$matchThese['contact_no'],'email'=>'','about_us'=>'','city'=>'','state'=>'','locality'=>'','address'=>'','category'=>'','category_ids'=>0,'category_json'=>$category_json,'data_source'=>'','profile_pic'=>'','call_time'=>'','latitude'=>'','longitude'=>'','cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'1','created_at'=>date('Y-m-d H:i:s'),'pincode'=>'','referal_code'=>'','piggy_bal'=>$piggybal,'is_installed'=>'1');
		$data=CM4UserProfile::create($userinfo);
			  
			  //Create User Piggy Ac
		 $piggybankdata = [
                     "user_name" => '',
                     "contact_no" => $matchThese['contact_no'],
                     "address" => '',
                     "uid" =>$data->id,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
					 "amt_earned"=>'5.00'
                     ];
            CM4PiggyBankAccount::create($piggybankdata);	
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$data->id];	
		  $Refer = CM4UserRefer::where($check_user)->get(['id']);
			if(count($Refer)==0)
			{
		$cm4_user_refers=array('uid'=>$data->id,'refer_code'=>$referal,'earned_by_uid'=>'','created_at'=>date('Y-m-d H:i:s'));	  
			$datarefer=CM4UserRefer::create($cm4_user_refers);
			}
			  
			
                $finalData=['user_registration_status'=>'0','userdetailsstatus'=>$userdetailsstatus,'user'=>[
                    'id'=>$data->id,
                    'user_id'=>$userId,
                    'name'=>'',
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/noImage.png",
                    'gender'=>'',
                    'locality'=>'',
                    'age'=>'',
                    'address'=>'',
                    'country'=>'',
                    'city'=>'',
                    'state'=>'',
                    'latitude'=>'',
                    'longitude'=>'',
                    'call_time'=>'Monday|10:00 AM - 6:00 PM,Tuesday|10:00 AM - 6:00 PM,Wednesday|10:00 AM - 6:00 PM,Thursday|10:00 AM - 6:00 PM,Friday|10:00 AM - 6:00 PM,Satuday|10:00 AM - 6:00 PM,Sunday|10:00 AM - 6:00 PM',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>'',
                    'contact_no'=>$matchThese['contact_no'],
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>$requestData['device_id'],
                    'cc_password'=>$pass,
                    'email'=>'',
                    'cc_fdail'=>$fdial,
                    'category'=>'',
                    'piggy_bal'=>0,
                    'live_status'=>1,
                    'referal_code'=>$referal,
                    'update_profile_status'=>0,
                    'data_source'=>'',
					'address2'=>'',
					'per_min_val'=>'5.00',
					'is_logged_from_another_device'=>'0',
					'user_searchid'=>'',
					'youtube_link'=>'',
					'facebook_link'=>'',
					'twitter_link'=>'',
					'instagram_link'=>'',
					'snapchat_link'=>'',
					'blog_link'=>'',
					'msg_bf_call'=>'',
					'more_about'=>'',
					'reviewcount'=>0,
					'avgrating'=>0,
					'callcount'=>0
					
               ]];
               
			   $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
              
           }else{

                //update live status	
			 $contact_no=$mobile;
			
			CM4UserProfile::where('contact_no',$contact_no)->update(['live_status' =>1,'is_installed'=>1]);
				
				//Check Social Data
				$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0)
				{
				   $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}	
				else
				{
				    $youtube_link='';
					$facebook_link='';
					$twitter_link='';
					$instagram_link='';
					$snapchat_link='';
					$blog_link='';
					$msg_bf_call='';
					$more_about='';		
				}		
				
				
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		 $searched_uid=$user[0]->id;
		 $searched_contact=$user[0]->contact_no;	
		  $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
		  $raterevqryex= \ DB::select($rate_rev_qry);
			$reviewcount=0;
			$avgrating=0;
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
				
				
				if($requestData['device_id']==$user[0]->device_id){

                  
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];

                   // $user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";

                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                    $user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					
					
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                 

                }else{

                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device'] = "1";

                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                    //$user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";
                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['service']=$user[0]['category'];
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
						else
						{
						$user[0]['service_ids']=array();	
						}
					$user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					
					
                    unset($user[0]['category_ids']);
                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?0:1,'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                    
                }
            }

        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
          
        }

        return response()->json($result, 200);
    }

	
	//GET USER DATA
	public function getMyProfile() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
		
		
		} else {

            $requestData = Request::all();
        }
	
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('id', $requestData)
            
        )) {
          
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $requestData['phone'],
            'id' => $requestData['id']
        ];
        $rules = [
            'phone' => 'required',
            'id' => 'required',
            
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            
        }
		//change 
		$mobile ='91'.$requestData['phone'];
		 //Check User Details Status
		$matchThese = ['contact_no' =>$mobile];
		$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
			
		 		if($status>0)
				{
				//Check Social Data
				$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0)
				{
				   $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}	
				else
				{
				    $youtube_link='';
					$facebook_link='';
					$twitter_link='';
					$instagram_link='';
					$snapchat_link='';
					$blog_link='';
					$msg_bf_call='';
					$more_about='';		
				}		
				
			$searched_uid=$user[0]->id;	
		     //Get Force Rate Update
		
		$today_date=date('Y-m-d');
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$user[0]['force_close']='1';	
			}
			else
			{
			$user[0]['force_close']='0';	
			}
			 
			//Call Time Today	
			
			if($user[0]['call_time']!="") 
	       {
            $time= $this->today_timing($user[0]['call_time']);
            $time=str_replace("-","|",$time);
			$user[0]['today_timing']=$time;
           }
		 else
		  {
			$user[0]['today_timing']="";
		  }
			 
			 //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		 
		 $searched_contact=$user[0]->contact_no;	
		  $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
		  $raterevqryex= \ DB::select($rate_rev_qry);
			$reviewcount=0;
			$avgrating=0;
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
				   $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];

                   // $user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";

                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                    $user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                }
             else 
			 {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
			}

        return response()->json($result, 200);
    }
    
	 public function verifySms1() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
 if (Request::header('content-type') == "application/json") {
         $requestData = Request::json()->all();
		} else 
         {
         $requestData = Request::all();
        }
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('code', $requestData)
            && array_key_exists('device_id', $requestData)
            && array_key_exists('country_code', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'phone' => $requestData['phone'],
            'code' => $requestData['code'],
            'device_id' => $requestData['device_id'],
            'country_code' => $requestData['country_code']
        ];
        $rules = [
            'phone' => 'required',
            'code' => 'required|numeric|digits:4',
            'device_id' => 'required',
            'country_code' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }

        $matchThese = ['phone' => $requestData['phone'],'device_id'=>$requestData['device_id'],'code'=>$requestData['code']];
       $userinfo = CM4UserInfo::where($matchThese)->get(['id']);
        $status = $userinfo->count();
          if ($status != 0) 
              {
            $userinfoupdate = CM4UserInfo::find($userinfo[0]['id']);
	    $userinfoupdate->status = 1;
            $userinfoupdate->save();
            $matchThese = ['contact_no' => $userinfoupdate->phone];
            $user = CM4UserProfile::where($matchThese)->get(['id','user_id','device_id','call_time','address','user_name','category','category_ids','category_json','contact_person','profile_pic','gender','age','about_us','profile_status','is_installed','piggy_bal','user_rating','contact_no']);
            $status = $user->count();
            $phone= '91'.$requestData['phone'];
            $email="";
            $name="";
            if($status==0){
		$referal=$this->gen_referal_code();
                $genData= $this->register($phone,$email,$name);
                $userId=$genData['user_id'];
                $fdial=$genData['cc_fdial'];
                $pass=$genData['cc_password'];	
		$piggybal="5.00";   
	$category_json="a:0:{}";
	$userprofile=array('user_id'=>$userId,'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$matchThese['contact_no'],'email'=>'','about_us'=>'','city'=>'','state'=>'','locality'=>'','address'=>'','category'=>'','category_ids'=>0,'category_json'=>$category_json,'data_source'=>'','profile_pic'=>'','call_time'=>'','latitude'=>'','longitude'=>'','cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'1','created_at'=>date('Y-m-d H:i:s'),'pincode'=>'','referal_code'=>'','piggy_bal'=>$piggybal,'is_installed'=>'1');
		$data=CM4UserProfile::create($userprofile);
			  
			  //Create User Piggy Ac
		 $piggybankdata = [
                     "user_name" => '',
                     "contact_no" => $matchThese['contact_no'],
                     "address" => '',
                     "uid" =>$data->id,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
                    "amt_earned"=>'5.00'
                     ];
            CM4PiggyBankAccount::create($piggybankdata);	
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$data->id];	
		  $userrefer = CM4UserRefer::where($check_user)->get(['id']);
			if(count($userrefer)==0)
			{
		$cm4_user_refers=array('uid'=>$data->id,'refer_code'=>$referal,'earned_by_uid'=>'','created_at'=>date('Y-m-d H:i:s'));	  
			$datarefer=CM4UserRefer::create($cm4_user_refers);
			}
			  
			  //working
	// CM4TempAppUserData::where('contact_no',$matchThese['contact_no'])->update(['is_installed' =>1,'install_date'=>date('Y-m-d H:i:s')]);
				
                $finalData=['user_registration_status'=>"0",'user'=>[
                    'id'=>$data->id,
                    'user_id'=>$userId,
                    'name'=>'',
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/noImage.png",
                    'gender'=>'',
                    'locality'=>'',
                    'age'=>'',
                    'address'=>'',
                    'country'=>'',
                    'city'=>'',
                    'state'=>'',
                    'latitude'=>'',
                    'longitude'=>'',
                    'call_time'=>'',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>'',
                    'contact_no'=>$matchThese['contact_no'],
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>$requestData['device_id'],
                    'cc_password'=>$pass,
                    'email'=>'',
                    'cc_fdail'=>$fdial,
                    'category'=>'',
                    'piggy_bal'=>0,
                    'live_status'=>1,
                    'referal_code'=>$referal,
                    'update_profile_status'=>0,
                    'data_source'=>''
                ]];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
             
            }else{

                //update live status	
                $contact_no='91'.$requestData['phone'];
               CM4UserProfile::where('contact_no',$contact_no)->update(['live_status' =>1,'is_installed'=>1]);
               if($requestData['device_id']==$user[0]->device_id)
                   {
                  $time =explode('|',$user[0]['call_time']);
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                  if($user[0]['profile_pic']!='') {
                   $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['start_time']=$time[0];
                    $user[0]['end_time']=isset($time[1])?$time[1]:"";
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                  
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                 
                }else{

                    $time =explode('|',$user[0]['call_time']);
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device'] = "1";

                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['start_time']=$time[0];
                    $user[0]['end_time']=isset($time[1])?$time[1]:"";

                    $user[0]['service']=$user[0]['category'];
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
						else
						{
						$user[0]['service_ids']=array();	
						}

		 unset($user[0]['category_ids']);
                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?0:1,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);     
                }
            }

        } else {
           $result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
        }

        return response()->json($result, 200);
    }
	
	
	//Verify SMS Added some sim No and Other Details.
	public function verifySmsnew() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
		
		
		} else {

            $requestData = Request::all();
        }
	
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('code', $requestData)
            && array_key_exists('device_id', $requestData)
            && array_key_exists('country_code', $requestData)
        )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 4) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $requestData['phone'],
            'code' => $requestData['code'],
            'device_id' => $requestData['device_id'],
            'country_code' => $requestData['country_code']
        ];
        $rules = [
            'phone' => 'required',
            'code' => 'required|numeric|digits:4',
            'device_id' => 'required',
            'country_code' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            
        }
		$mobile='91'.$requestData['phone'];
		 //Check User Details Status
		$matchThese = ['contact_no' => $mobile];
		$userdetails = CM4UsersDetails::where($matchThese)->get(['id']);
        $userdetailsstatus = $userdetails->count();
		//Change
		
        $matchThese = ['phone' => $mobile,'device_id'=>$requestData['device_id'],'code'=>$requestData['code']];

        $user = CM4UserInfo::where($matchThese)->get(['id']);
        $status = $user->count();

        //-------sim detail-----------------------------------//
            
      
		if ($status != 0) {

         $user = CM4UserInfo::find($user[0]['id']);
     
			$user->status = 1;
            $user->save();

            $matchThese = ['contact_no' => $user->phone];
            $user = CM4UserProfile::where($matchThese)->get();

            $matchThese1 = ['contact_no' => $mobile];
            $user_sim = CM4UserSimno::where($matchThese1)->get();
            if(count($user_sim)!=0){
				$sim_number=$user_sim[0]->sim_number;
			}else{
				$sim_number='';
			}
         
		$status = $user->count();
			
		 $phone= $mobile;
            $email="";
            $name="";
            if($status==0){
				$referal=$this->gen_referal_code();
                $genData= $this->register($phone,$email,$name);
				//return $genData;	
                $userId=$genData['user_id'];
                $fdial=$genData['cc_fdial'];
                $pass=$genData['cc_password'];
				$piggybal="5.00";   
	$category_json="a:0:{}";
	$userinfo=array('user_id'=>$userId,'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$matchThese['contact_no'],'email'=>'','about_us'=>'','city'=>'','state'=>'','locality'=>'','address'=>'','category'=>'','category_ids'=>0,'category_json'=>$category_json,'data_source'=>'','profile_pic'=>'','call_time'=>'','latitude'=>'','longitude'=>'','cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'1','created_at'=>date('Y-m-d H:i:s'),'pincode'=>'','referal_code'=>'','piggy_bal'=>$piggybal,'is_installed'=>'1');
		$data=CM4UserProfile::create($userinfo);
			  
			  //Create User Piggy Ac
		 $piggybankdata = [
                     "user_name" => '',
                     "contact_no" => $matchThese['contact_no'],
                     "address" => '',
                     "uid" =>$data->id,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
					 "amt_earned"=>'5.00'
                     ];
            CM4PiggyBankAccount::create($piggybankdata);	
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$data->id];	
		  $Refer = CM4UserRefer::where($check_user)->get(['id']);
			if(count($Refer)==0)
			{
		$cm4_user_refers=array('uid'=>$data->id,'refer_code'=>$referal,'earned_by_uid'=>'','created_at'=>date('Y-m-d H:i:s'));	  
			$datarefer=CM4UserRefer::create($cm4_user_refers);
			}
			  
			
                $finalData=['user_registration_status'=>'0','userdetailsstatus'=>$userdetailsstatus,'user'=>[
                    'id'=>$data->id,
                    'user_id'=>$userId,
                    'name'=>'',
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/noImage.png",
                    'gender'=>'',
                    'locality'=>'',
                    'age'=>'',
                    'address'=>'',
                    'country'=>'',
                    'city'=>'',
                    'state'=>'',
                    'latitude'=>'',
                    'longitude'=>'',
                    'call_time'=>'Monday|10:00 AM - 6:00 PM,Tuesday|10:00 AM - 6:00 PM,Wednesday|10:00 AM - 6:00 PM,Thursday|10:00 AM - 6:00 PM,Friday|10:00 AM - 6:00 PM,Satuday|10:00 AM - 6:00 PM,Sunday|10:00 AM - 6:00 PM',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>'',
                    'contact_no'=>$matchThese['contact_no'],
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>$requestData['device_id'],
                    'cc_password'=>$pass,
                    'email'=>'',
                    'cc_fdail'=>$fdial,
                    'category'=>'',
                    'piggy_bal'=>0,
                    'live_status'=>1,
                    'referal_code'=>$referal,
                    'update_profile_status'=>0,
                    'data_source'=>'',
					'address2'=>'',
					'per_min_val'=>'5.00',
					'is_logged_from_another_device'=>'0',
					'user_searchid'=>'',
					'youtube_link'=>'',
					'facebook_link'=>'',
					'twitter_link'=>'',
					'instagram_link'=>'',
					'snapchat_link'=>'',
					'blog_link'=>'',
					'msg_bf_call'=>'',
					'more_about'=>'',
					'reviewcount'=>0,
					'avgrating'=>0,
					'callcount'=>0,
					'simno'=>$sim_number,
					'other_contact_no'=>''
               ]];
               
			   $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
              
           }else{

                //update live status	
			 $contact_no='91'.$requestData['phone'];
			
			CM4UserProfile::where('contact_no',$contact_no)->update(['live_status' =>1,'is_installed'=>1]);
				
				//Check Social Data
				$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0)
				{
				   $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}	
				else
				{
				    $youtube_link='';
					$facebook_link='';
					$twitter_link='';
					$instagram_link='';
					$snapchat_link='';
					$blog_link='';
					$msg_bf_call='';
					$more_about='';		
				}		
				
				
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		 $searched_uid=$user[0]->id;
		 $searched_contact=$user[0]->contact_no;	
		  $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
		  $raterevqryex= \ DB::select($rate_rev_qry);
			$reviewcount=0;
			$avgrating=0;
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
				
				
				if($requestData['device_id']==$user[0]->device_id){

                  
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];

                   // $user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";

                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                    $user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					$user[0]['simno']=$sim_number;
					$user[0]['other_contact_no']=$user[0]->marital_status;
					
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                 

                }else{

                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device'] = "1";

                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                    //$user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";
                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['service']=$user[0]['category'];
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
						else
						{
						$user[0]['service_ids']=array();	
						}
					$user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					$user[0]['simno']=$sim_number;
					$user[0]['other_contact_no']=$user[0]->marital_status;
					
                    unset($user[0]['category_ids']);
                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?0:1,'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                    
                }
            }

        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
          
        }

        return response()->json($result, 200);
    }

	
	
	/**
     * generate fdial key from Number.
     *
     * @return Response
     */
   public function convertFdialkey($number) {
        $encoded_number = md5($number);
        $en_int_number = '';
        for ($i = 0; $i < strlen($encoded_number); $i++) {
            $char = substr($encoded_number, $i, 1);
            if (strlen($en_int_number) == 10) {
                break;
            }
            if (is_numeric($char)) {
                $en_int_number .= $char;
            }
        }
        return $en_int_number;
    }

   public function MDP_NUMERIC($chrs = 10) {
        $myrand = "";
        for ($i = 0; $i < $chrs; $i++) {
            $myrand .= mt_rand(0, 9);
        }

        return $myrand;
    }

   public function MDP_STRING($chrs = 20) {
        $pwd = "";
        mt_srand((double) microtime() * 1000000);
        while (strlen($pwd) < $chrs) {
            $chr = chr(mt_rand(0, 255));
            if (preg_match("/^[0-9a-z]$/i", $chr))
                $pwd = $pwd . $chr;
        };
        return strtolower($pwd);
    }

    /**
     * @param string $table
     * @return array|bool
     */
    public function gen_card_with_alias($table = "cc_card") {
        $ctr=0;

        $flag = true;
        while ($flag) {
            $ctr++;

            $card_gen = $this->MDP_NUMERIC(10);
            $alias_gen = $this->MDP_NUMERIC(15);

            /*$data =\DB::connection('a2billing')->table('cc_card')
           ->where('username', '=', $card_gen)
               ->get(['id']);*/
			$data =\DB::table('cm4_user_profile')
           ->where('user_id', '=', $card_gen)
               ->get(['id']);
            if (count($data) > 0)
                continue;

            $data =\DB::connection('a2billing')->table('cc_card')
                ->where('useralias', '=', $alias_gen)
                ->get(['id']);
            if (count($data) > 0)
                continue;

            $flag = false;
            if ($ctr == 1000)
                return false;
        }

        $pass = $this->MDP_NUMERIC(10);
        $loginkey = $this->MDP_STRING(20);
        return array('user' => $card_gen, 'useralias' => $alias_gen, 'pass' => $pass, 'loginkey' => $loginkey);
    }

    public function gen_activation_code($table = "cc_card") {
       $ctr=0;

        $card_gen = "";
        $flag = true;
        while ($flag) {
            $ctr++;
            $card_gen = $this->MDP_STRING(8);


            $data =\DB::connection('a2billing')->table('cc_card')
                ->where('activationCode', '=', $card_gen)
                ->get(['id']);
            if (count($data) > 0)
                continue;

            if ($ctr == 1000)
                return false;
            $flag = false;
        }

        return ($card_gen) ? $card_gen : false;
    }

    public function search() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('text', $requestData)
            &&array_key_exists('latitude', $requestData)
            &&array_key_exists('longitude', $requestData)
            &&array_key_exists('distance', $requestData)
            &&array_key_exists('city', $requestData)
            &&array_key_exists('filter', $requestData)
            &&array_key_exists('uid', $requestData)
            &&array_key_exists('start', $requestData)
            &&array_key_exists('rows', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 9) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	
        $fields = [
            'text' => $requestData['text'],
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'distance' => $requestData['distance'],
            'city' => $requestData['city'],
            'uid' => $requestData['uid']
        ];
        $rules = [
            'text' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'distance' => 'required',
            'city' => 'required',
            'uid' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }
     
	 $text = preg_replace('!\s+!', '+', $requestData['text']);
        $userId=$requestData['uid'];
        $city=$requestData['city'];
        $lat=$requestData['latitude'];
        $long=$requestData['longitude'];
        $distance=$requestData['distance'];
        $start=$requestData['start'];
        $rows=$requestData['rows'];
        $pt            = $lat . "," . $long;

     
 if($requestData['filter']!=""){
    $filter= $requestData['filter'];
      $filter_all=' AND -tags:"'.urlencode($filter).'"';
	$filter_arr= explode(',',$filter);
    // return $filter_arr;
     $filter_str="";
     foreach($filter_arr as $value)
         $filter_str.=' AND -tags:"'.$value.'"';
		$filterval=urlencode($filter_str);
	
     $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags
      &defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&fq=({!geofilt pt=$pt
      sfield=geolocation d=$distance}$filterval$filter_all)&sort=geodist(geolocation,$lat,$long)+asc";
 }else{
     
   $findme   = '@cm4';
$pos = strpos($text,$findme);  
		if ($pos !== false) 
		{
    $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=geodist(geolocation,$lat,$long)+asc";
		} 
	 else
	 {
	 $distance='500';
	 $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&fq={!geofilt pt=$pt
      sfield=geolocation d=$distance}&sort=live_status desc,geodist(geolocation,$lat,$long)+asc";	 
	 }
 }
//echo $details_url;die;
 $details_url = preg_replace('!\s+!', '+', $details_url);
       //return $details_url;
        $response    = file_get_contents($details_url);
       $response = json_decode($response, true);
      
	   // $coll_res =(object)$response;
    $response["responseHeader"]["params"]["fq"]="CallMe4";

$response_arr= $response["response"]["docs"];
        $records=[];
        $tags =[];
    
	  foreach($response_arr as $val){
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="" && $val['live_status']==0) {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']=$val['call_time'];
            }
            if(  array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }else{
                $val['tags']="";
            }
            $val['dist']=$val['dist'];
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		  
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		}
			
			if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            array_push($records,$val);
            array_push($tags, $val['tags']);
        }
		
	   $list = implode(',',array_unique($tags));
        $newlist=explode(',',$list);
		$newtags=implode(',',array_unique($newlist));
		$newtags=trim($newtags,",");	
		$response["response"]["tags"]=$newtags;
        $response["response"]["docs"]=$records;

     //   return $response;
	 $data = array("text" => $text,
                "uid" => $userId,
                "locality" =>$city,
                "latitude" =>$lat,
                "longitude" =>$long,
				"record_count"=>$response["response"]["numFound"],
				"distance"=>$distance
			);
		CM4Search::create($data); 
           
        if ($response["response"]["numFound"]!=0) {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response, "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }

    //Testing
 
 public function searchnew() {
      
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('text', $requestData)
            &&array_key_exists('uid', $requestData)
            &&array_key_exists('start', $requestData)
            &&array_key_exists('rows', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) < 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	
        $fields = [
            'text' => $requestData['text'],
            'uid' => $requestData['uid']
        ];
        $rules = [
            'text' => 'required',
             'uid' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
     
	 $text = preg_replace('!\s+!', '+', $requestData['text']);
        $userId=$requestData['uid'];
        $start=$requestData['start'];
        $rows=$requestData['rows'];
       
  $records=[];
   $tags =[];
  $newarray=array();
  $blogger_ids="";
     
 if($requestData['filter']!=""){
    $filter= $requestData['filter'];
      $filter_all=' AND -tags:"'.urlencode($filter).'"';
	$filter_arr= explode(',',$filter);
    // return $filter_arr;
     $filter_str="";
     foreach($filter_arr as $value)
         $filter_str.=' AND -tags:"'.$value.'"';
		$filterval=urlencode($filter_str);
	
     $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags
      &defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&fq=({!geofilt pt=$pt
      sfield=geolocation d=$distance}$filterval$filter_all)&sort=geodist(geolocation,$lat,$long)+asc";
 }else{
     $premium_response["response"]["numFound"]=0;
   $findme   = '@cm4';
   $pos = strpos($text,$findme);  
	
	//For Youtubers
	$popular_youtube_ids="";
	//For SSC Exam Preparation
	$ssc_ids="";
	$board_ids="";
	if($text=='Popular+Youtubers')
	{
	$ids=[602638,
981564,
982028,
982384,
982594,
982924,
983251,
983794,
983799,
984056,
984295,
986157,
986263,
986276,
986339,
986350,
986402,
986404,
986535,
986587,
986953,
987266,
987310,
987596,
988276,
988319,
988431,
988605,
988700,
988835,
988949,
989659,
990881,
992170,
992804,
992908,
993489,
993504,
993820,
993891,
994031,
994305,
994451,
994525,
994915,
995140,
995409,
996401,
997006,
997074,
998024,
998111,
998428,
998640,
999473,
999524,
999621,
999681,
999707,
1000137,
1000664,
1000823,
1001095,1002162,1000871];	
	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$popular_youtube_ids.="id:$id";	
			}	
			else
			{
		$popular_youtube_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($popular_youtube_ids!="")
    {
	$popular_id="($popular_youtube_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	else if($text=='SSC+Exam+Preparation')
	{
	$ids=[986404,993489,994703];	

	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$ssc_ids.="id:$id";	
			}	
			else
			{
		$ssc_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($ssc_ids!="")
    {
	$popular_id="($ssc_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	//Board Exam Preparation
	else if($text=='Board+Exam+Preparation')
	{
	$ids=[1004441,994222,
994107,
1003328,
1002732,
1002647,
1001623,
1002665,
1002206,
1004301
];	

	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$board_ids.="id:$id";	
			}	
			else
			{
		$board_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($board_ids!="")
    {
	$popular_id="($board_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	
	else
	{
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	
	}
	
	
	  $premium_url = preg_replace('!\s+!', '+', $premium_url);
	
	  //return $details_url;
       $premium_response    = file_get_contents($premium_url);
       $premium_response = json_decode($premium_response,true);
	 $premium_response_arr=  $premium_response["response"]["docs"];
	 if(count($premium_response_arr)>0)
	{	
	$count=0;
	foreach($premium_response_arr as $val){
		 
			if($count==0)
			{
			$blogger_ids.="-id:$val[id]";	
			}	
			else
			{
		$blogger_ids.=" AND -id:$val[id]";
			}
		 $filterids=urlencode($blogger_ids);
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="" ) {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }
			 else if($val['service']!="")
			{
			if (preg_match("/;/",$val['service'])) {
			
			$category=explode(';',$val['service']);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
			}
			} else {
		$category=explode(':',$val['service']);
		$searchtext[]=$category[0];
		}
	   
	    $categorytext=implode(",",$searchtext);
		unset($searchtext);
		//$categorytext=$searchtext[0];
			 $val['tags']=$categorytext;
			} 
			
			else {
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		/* //Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$val['force_close']='1';	
			}
			else
			{
			$val['force_close']='0';	
			}
		
		$val['is_premium']='1';	
		
			//Get Total Call call count received.
		
		$select_video="SELECT video_id,video_title,per_min_val,online_status,Is_youtube,is_verified from cm4_premium_customer where id='".$searched_uid."'";
		
		$getvideo= \ DB::select($select_video);
		if(count($getvideo)>0)
		{
			if($getvideo[0]->video_id!='') {
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
            }else{
                
				 $val['thumbnail_big']="";
				 $val['video_id']="";
            }
		$val['per_min_val']=$getvideo[0]->per_min_val;
		$val['online_status']=$getvideo[0]->online_status;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		$val['video_title']=$getvideo[0]->video_title;
		$val['is_verified']=$getvideo[0]->is_verified;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";
		$val['is_youtube']="0";	
		$val['video_title']="";
		$val['is_verified']="0";
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		
		//Select Profile Pic
		$select_profile_pic="SELECT profile_pic from cm4_user_profile where id='".$searched_uid."' and is_installed='1'";
		
		$getimage= \ DB::select($select_profile_pic);
		if(count($getimage)>0)
		{
			if($getimage[0]->profile_pic!='') {
				$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $getimage[0]->profile_pic;
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
		}
		else
		{
		$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;	
		}
		//END OF PROFLE PIC
			
			/* if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            } */

            array_push($records,$val);
            array_push($tags, $val['tags']);
		$count ++;
	 }
	}
	$searchuser_id="";
	if($blogger_ids!="")
    {
	$searchuser_id=	"&fq=($blogger_ids)";
	}	
	$distance=500;	
	 $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)$searchuser_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	 
	
	 }
 
//echo $details_url;die;
 $details_url = preg_replace('!\s+!', '+', $details_url);
       //return $details_url;
        $response    = file_get_contents($details_url);
       $response = json_decode($response, true);
      
	   // $coll_res =(object)$response;
    $response["responseHeader"]["params"]["fq"]="CallMe4";
	$response_arr= $response["response"]["docs"];
    foreach($response_arr as $val){
		  
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="") {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }else{
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		  
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		/* $callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$val['force_close']='1';	
			}
			else
			{
			$val['force_close']='0';	
			}
		
		//GET PERMINUTE VALUE AND ONLINE STATUS
		$querystatus="SELECT per_min_val,is_callback as online_status from cm4_user_profile where id='".$searched_uid."'";
		$status_query_ex= \ DB::select($querystatus);
		
		if(count($status_query_ex)>0)
		{
		$val['per_min_val']=$status_query_ex[0]->per_min_val;
		$val['online_status']=$status_query_ex[0]->online_status;
		
		}
		else
		{
		$val['per_min_val']="0";
		$val['online_status']="1";	
		}	
		$val['is_premium']='0';	
		
			//Get Total Call call count received.
		$val['is_youtube']="0";
		$select_video="SELECT video_id,video_title,Is_youtube from cm4_premium_customer where id='".$searched_uid."'";
		
		$getvideo= \ DB::select($select_video);
		
		if(count($getvideo)>0)
		{
			if(isset($getvideo[0]->video_id) && $getvideo[0]->video_id!='') {
				$video_id=$getvideo[0]->video_id;
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
				$val['video_title']=$getvideo[0]->video_title;
			
            }else{
                 $val['thumbnail_big']="";
				 $val['video_id']="";
				 $val['video_title']="";
            }
		//print_r($val);die;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		$val['is_verified']=$getvideo[0]->is_verified;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";	
		$val['video_title']="";
		$val['is_verified']="0";
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		//Select Profile Pic
		$select_profile_pic="SELECT profile_pic from cm4_user_profile where id='".$searched_uid."' and is_installed='1'";
		
		$getimage= \ DB::select($select_profile_pic);
		if(count($getimage)>0)
		{
			if($getimage[0]->profile_pic!='') {
				$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $getimage[0]->profile_pic;
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
		}
		else
		{
		$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;	
		}
		//END OF PROFLE PIC
			
		/* if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            } */

            array_push($records,$val);
            array_push($tags, $val['tags']);
        }
		
	   $list = implode(',',array_unique($tags));
        $newlist=explode(',',$list);
		$newtags=implode(',',array_unique($newlist));
		$newtags=trim($newtags,",");	
		$response["response"]["tags"]=$newtags;
        $response["response"]["docs"]=$records;
		
     $total_record=$response["response"]["numFound"] + $premium_response["response"]["numFound"];
	  $response["response"]["numFound"]=$total_record;
	 //   return $response;
	 $data = array("text" => $text,
                "uid" => $userId,
                "record_count"=>$response["response"]["numFound"],
			);
		//CM4Search::create($data); 
           
        if ($total_record!=0) {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response, "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }


	   //Testing
 public function searchios() {
      
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('text', $requestData)
            &&array_key_exists('uid', $requestData)
            &&array_key_exists('start', $requestData)
            &&array_key_exists('rows', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) < 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	
        $fields = [
            'text' => $requestData['text'],
            'uid' => $requestData['uid']
        ];
        $rules = [
            'text' => 'required',
             'uid' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
     
	 $text = preg_replace('!\s+!', '+', $requestData['text']);
        $userId=$requestData['uid'];
        $start=$requestData['start'];
        $rows=$requestData['rows'];
       
  $records=[];
   $tags =[];
  $newarray=array();
  $blogger_ids="";
     
 if($requestData['filter']!=""){
    $filter= $requestData['filter'];
      $filter_all=' AND -tags:"'.urlencode($filter).'"';
	$filter_arr= explode(',',$filter);
    // return $filter_arr;
     $filter_str="";
     foreach($filter_arr as $value)
         $filter_str.=' AND -tags:"'.$value.'"';
		$filterval=urlencode($filter_str);
	
     $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags
      &defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&fq=({!geofilt pt=$pt
      sfield=geolocation d=$distance}$filterval$filter_all)&sort=geodist(geolocation,$lat,$long)+asc";
 }else{
     $premium_response["response"]["numFound"]=0;
   $findme   = '@cm4';
   $pos = strpos($text,$findme);  
	
	//For Youtubers
	$popular_youtube_ids="";
	//For SSC Exam Preparation
	$ssc_ids="";
	$board_ids="";
	if($text=='Popular+Youtubers')
	{
	
	$ids=[1006361,602638,
981564,
982028,
982384,
982594,
982924,
983251,
983794,
983799,
984056,
984295,
986157,
986263,
986276,
986339,
986350,
986402,
986404,
986535,
986587,
986953,
987266,
987310,
987596,
988276,
988319,
988431,
988605,
988700,
988835,
988949,
989659,
990881,
992170,
992804,
992908,
993489,
993504,
993820,
993891,
994031,
994305,
994451,
994525,
994915,
995140,
995409,
996401,
997006,
997074,
998024,
998111,
998428,
998640,
999473,
999524,
999621,
999681,
999707,
1000137,
1000664,
1000823,
1001095,1002162,1000871];	
	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$popular_youtube_ids.="id:$id";	
			}	
			else
			{
		$popular_youtube_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($popular_youtube_ids!="")
    {
	$popular_id="($popular_youtube_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	else if($text=='SSC+Exam+Preparation')
	{
	$ids=[986404,993489,994703];	

	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$ssc_ids.="id:$id";	
			}	
			else
			{
		$ssc_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($ssc_ids!="")
    {
	$popular_id="($ssc_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	//Board Exam Preparation
	else if($text=='Board+Exam+Preparation')
	{
	$ids=[1004441,994222,
994107,
1003328,
1002732,
1002647,
1001623,
1002665,
1002206,
1004301,
1010914
];	

	$count=0;
	foreach($ids as $id)
	{
	if($count==0)
			{
		$board_ids.="id:$id";	
			}	
			else
			{
		$board_ids.=" OR id:$id";
			}
	$count ++;
	}
	$popular_id="";
	if($board_ids!="")
    {
	$popular_id="($board_ids)";
	}	
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
	}
	
	else
	{
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	
	}
	
	
	  $premium_url = preg_replace('!\s+!', '+', $premium_url);
	
	  //return $details_url;
       $premium_response    = file_get_contents($premium_url);
       $premium_response = json_decode($premium_response,true);
	 $premium_response_arr=  $premium_response["response"]["docs"];
	 if(count($premium_response_arr)>0)
	{	
	$count=0;
	foreach($premium_response_arr as $val){
		 
			if($count==0)
			{
			$blogger_ids.="-id:$val[id]";	
			}	
			else
			{
		$blogger_ids.=" AND -id:$val[id]";
			}
		 $filterids=urlencode($blogger_ids);
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="" ) {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }
			 else if($val['service']!="")
			{
			if (preg_match("/;/",$val['service'])) {
			
			$category=explode(';',$val['service']);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
			}
			} else {
		$category=explode(':',$val['service']);
		$searchtext[]=$category[0];
		}
	   
	    $categorytext=implode(",",$searchtext);
		unset($searchtext);
		//$categorytext=$searchtext[0];
			 $val['tags']=$categorytext;
			} 
			
			else {
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		/* //Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$val['force_close']='1';	
			}
			else
			{
			$val['force_close']='0';	
			}
		
		$val['is_premium']='1';	
		
			//Get Total Call call count received.
		$select_video="SELECT video_id,video_title,per_min_val,online_status,Is_youtube from cm4_premium_customer where id='".$searched_uid."'";
		
		$getvideo= \ DB::select($select_video);
		if(count($getvideo)>0)
		{
			if($getvideo[0]->video_id!='') {
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
				//$val['thumbnail_big']="";
				 //$val['video_id']="";
            }else{
                
				 $val['thumbnail_big']="";
				 $val['video_id']="";
            }
		$val['per_min_val']=$getvideo[0]->per_min_val;
		$val['online_status']=$getvideo[0]->online_status;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		$val['video_title']=$getvideo[0]->video_title;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";
		$val['is_youtube']="0";	
		$val['video_title']="";
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		
		
			if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            array_push($records,$val);
            array_push($tags, $val['tags']);
		$count ++;
	 }
	}
	$searchuser_id="";
	if($blogger_ids!="")
    {
	$searchuser_id=	"&fq=($blogger_ids)";
	}	
	$distance=500;	
	 $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)$searchuser_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	 
	
	 }
 
//echo $details_url;die;
 $details_url = preg_replace('!\s+!', '+', $details_url);
       //return $details_url;
        $response    = file_get_contents($details_url);
       $response = json_decode($response, true);
      
	   // $coll_res =(object)$response;
    $response["responseHeader"]["params"]["fq"]="CallMe4";
	$response_arr= $response["response"]["docs"];
    foreach($response_arr as $val){
		  
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="") {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(  array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }else{
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		  
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		/* $callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$val['force_close']='1';	
			}
			else
			{
			$val['force_close']='0';	
			}
		
		//GET PERMINUTE VALUE AND ONLINE STATUS
		$querystatus="SELECT per_min_val,is_callback as online_status from cm4_user_profile where id='".$searched_uid."'";
		$status_query_ex= \ DB::select($querystatus);
		
		if(count($status_query_ex)>0)
		{
		$val['per_min_val']=$status_query_ex[0]->per_min_val;
		$val['online_status']=$status_query_ex[0]->online_status;
		
		}
		else
		{
		$val['per_min_val']="0";
		$val['online_status']="1";	
		}	
		$val['is_premium']='0';	
		
			//Get Total Call call count received.
		$val['is_youtube']="0";
		$select_video="SELECT video_id,video_title,Is_youtube from cm4_premium_customer where id='".$searched_uid."'";
		
		$getvideo= \ DB::select($select_video);
		
		if(count($getvideo)>0)
		{
			if(isset($getvideo[0]->video_id) && $getvideo[0]->video_id!='') {
				$video_id=$getvideo[0]->video_id;
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
				//$val['thumbnail_big']="";
				 //$val['video_id']="";
				$val['video_title']=$getvideo[0]->video_title;
			
            }else{
                 $val['thumbnail_big']="";
				 $val['video_id']="";
				 $val['video_title']="";
            }
		//print_r($val);die;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";	
		$val['video_title']="";
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		
		if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            array_push($records,$val);
            array_push($tags, $val['tags']);
        }
		
	   $list = implode(',',array_unique($tags));
        $newlist=explode(',',$list);
		$newtags=implode(',',array_unique($newlist));
		$newtags=trim($newtags,",");	
		$response["response"]["tags"]=$newtags;
        $response["response"]["docs"]=$records;
		
     $total_record=$response["response"]["numFound"] + $premium_response["response"]["numFound"];
	  $response["response"]["numFound"]=$total_record;
	 //   return $response;
	 $data = array("text" => $text,
                "uid" => $userId,
                "record_count"=>$response["response"]["numFound"],
			);
		//CM4Search::create($data); 
           
        if ($total_record!=0) {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response, "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }


	
	
	
	//TELE CALLER CALL DURATION API
	    public function gettelecallercallduration() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('userMobileIMEI', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
			}
     
					
		$userMobileIMEI = $requestData['userMobileIMEI'];
		$calldate=$requestData['contacts'][0]['callDate'];
		$calldate=date("Y-m-d",strtotime($calldate));
		$matchThese = ['userMobileIMEI' => $requestData['userMobileIMEI'],'callDate' => $calldate];
		 $user = CM4TelecallDuration::whereRaw("userMobileIMEI='".$userMobileIMEI."' and date(callDate)='".$calldate."'")->get();
        $userCount=$user->count();
        if ($userCount > 0) 
		{
		 $user = CM4TelecallDuration::whereRaw("userMobileIMEI='".$userMobileIMEI."' and date(callDate)='".$calldate."'")->delete();
		}
	foreach($requestData['contacts'] as $value)
		{
		
       $durationarray=array('userMobileIMEI'=>$userMobileIMEI,'phoneNumber'=>$value['phoneNumber'],'callType'=>$value['callType'],'callDate'=>$value['callDate'],'callduration'=>$value['callduration']);
	   
	   $data=CM4TelecallDuration::create($durationarray);
		}
        if(count($data)>0)
		{
        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);
		}
		else
		{
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>array(), "device_key" => $token]);	
		}
        return response()->json($result, 200);

    }
	
	/**
     * To update the user info.
     *
     * @return Response
     */
    public function updateUserInfo() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = date('Y-m-d : H:i:s');

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }

	if (!(array_key_exists('contact_person', $requestData)
            && array_key_exists('profile_pic', $requestData)
            && array_key_exists('id', $requestData)
            && array_key_exists('profession', $requestData)
            && array_key_exists('org_name', $requestData)
            && array_key_exists('age', $requestData)
            && array_key_exists('gender', $requestData)
            && array_key_exists('about_us', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('start_time', $requestData)
            && array_key_exists('end_time', $requestData)
            && array_key_exists('email', $requestData)
            && array_key_exists('address_source', $requestData)
            && array_key_exists('location', $requestData)
            && array_key_exists('locality', $requestData['location'])
            && array_key_exists('address', $requestData['location'])
            && array_key_exists('address2', $requestData['location'])
            && array_key_exists('pincode', $requestData['location'])
            && array_key_exists('city', $requestData['location'])
            && array_key_exists('state', $requestData['location'])
            && array_key_exists('country', $requestData['location'])
            && array_key_exists('latitude', $requestData['location'])
            && array_key_exists('longitude', $requestData['location'])
        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'id' => $requestData['id']
        ];
        $rules = [
            'id' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        //  return $value;

       // return $requestData;


        $data=   $this->imageUpload($requestData['profile_pic']);
			 if($requestData["address_source"]==1)
						   {//gps
                    $rec_addressgps=$requestData["location"]["address"];
						 $latitude = $requestData["location"]['latitude'];
                  $longtitude = $requestData["location"]['longitude'];  
						   }
				
				if($requestData["address_source"]==2)//manual
				{
				   $rec_address=$requestData["location"]["city"].
                        " ".$requestData["location"]["state"].
                        " ".$requestData["location"]["country"].
                        " ".$requestData["location"]["pincode"];

        $latLng=$this->get_lat_long($rec_address);
		$latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
		$matchThese = ['id' => $requestData['id']];
		//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
        
		$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();

        if ( $status  != 0) {


            $username = $requestData['contact_person'];
            $email = $requestData['email'];
            $profilePic = $data['name'];
            $gender = $requestData['gender'];
            $age = $requestData['age'];
            $lat = $latitude;
            $long = $longtitude;
            $addressSource = $requestData['address_source'];
            $address = $requestData['location']['address'];
            $address2 = $requestData['location']['address2'];
            $locality = $requestData['location']['locality'];
            $city = $requestData['location']['city'];
            $state = $requestData['location']['state'];
            $country = $requestData['location']['country'];
            $pincode = $requestData['location']['pincode'];
			$profession = $requestData['profession'];
            $workPlace = $requestData['org_name'];
			$aboutMe = $requestData['about_us'];
            $call_time = $requestData['start_time']."|".$requestData['end_time'];

			if($othersprofession=="")
				{
				 
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				}
				else
				{
				$getcategory=$requestData['others'];
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$requestData['others'];
				}		
			 }
				$category_json=serialize($profession);
				}	

            $current_rec = CM4UserProfile::find($user[0]['id']);
            $current_rec->user_name = $username;
            $current_rec->email = $email;
            $current_rec->contact_person = $username;
            if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
            $current_rec->gender = $gender;
            $current_rec->age = $age;
            $current_rec->latitude = $lat;
            $current_rec->longitude = $long;
            $current_rec->address_source = $addressSource;
            $current_rec->address = $address."|".$address2;
            $current_rec->city = $city;
            $current_rec->state = $state;
            $current_rec->country = $country;
            $current_rec->locality = $locality;
            $current_rec->profile_status = 1;
            $current_rec->category = $getcategory;
            $current_rec->category_ids =$list ;
            $current_rec->category_json =$category_json ;
            $current_rec->user_name = $workPlace;

            $current_rec->about_us = $aboutMe;
            $current_rec->call_time = $call_time;
            $current_rec->pincode = $pincode;
            $current_rec->update_profile_status = 1;
		if ($current_rec->save()) {
               $solrupdate=$this->_update_by_username_solr($requestData['id']);
				
				$matchThese = ['id' => $requestData['id']];

                $user = CM4UserProfile::where($matchThese)->get();
                //return $user;
                $time =explode('|',$user[0]['call_time']);
                $address =explode('|',$user[0]['address']);
                $finalData=[
                    'id'=>$user[0]['id'],
                    'user_id'=>$user[0]['user_id'],
                    'user_name'=>$user[0]['user_name'],
                    'org_name'=>$user[0]['user_name'],
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'],
                    'gender'=>$user[0]['gender'],
                    'locality'=>$user[0]['locality'],
                    'age'=>$user[0]['age'],
                    'address'=>$address[0],
                    'address2'=>$address[1],
                    'country'=>$user[0]['country'],
                    'city'=>$user[0]['city'],
                    'state'=>$user[0]['state'],
                    'latitude'=>$user[0]['latitude'],
                    'longitude'=>$user[0]['longitude'],
                    'start_time'=>$time[0],
                    'end_time'=>$time[1],
					'email'=>$user[0]['email'],
					'piggy_bal'  =>$user[0]['piggy_bal'],
                    'update_profile_status'  =>$user[0]['update_profile_status'],
                    'file_type'  =>'0',
                    'updated_at'  =>$user[0]['updated_at'],
                    'created_at'  =>$user[0]['created_at'],
                    'live_status'  =>$user[0]['live_status'],
                    'data_source'  =>$user[0]['data_source'],
                    'category_ids'  =>$user[0]['category_ids'],
                    'data_source'  =>$user[0]['data_source'],
                    'user_rating'  =>$user[0]['user_rating'],
                    'profile_status'  =>$user[0]['profile_status'],
                    'device_id'  =>$user[0]['device_id'],
                    'verification_status'  =>$user[0]['verification_status'],
                    'referal_code'  =>$user[0]['referal_code'],
                    'marital_status'  =>$user[0]['marital_status'],
                    'call_time'  =>$time[0]."|".$time[1],
                    'cc_fdail'  =>$user[0]['cc_fdail'],
                    'cc_password'  =>$user[0]['cc_password'],
                    'about_us'=>$user[0]['about_us'],
                    'user_rating'=>'',
                    'contact_person'=>$user[0]['contact_person'],
                    'contact_no'=>$user[0]['contact_no'],
                    'pincode'=>$user[0]['pincode'],
                    'service'=>$user[0]['category'],
                    'service_ids'=>unserialize($user[0]['category_json']),
                    'address_source'=>$user[0]['address_source']

                ];

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finalData, "device_key" => $token]);
            } else {

              //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }

    
		/**
     * To update the user profile.
     *date:07/01/2017
     *This is new function for update profile with user per min value.
	 * @return Response
     */
    public function updateUserProfile() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();
		$created_date = date('Y-m-d : H:i:s');

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }

		if (!(array_key_exists('contact_person', $requestData)
            && array_key_exists('profile_pic', $requestData)
            && array_key_exists('id', $requestData)
            && array_key_exists('profession', $requestData)
            && array_key_exists('contact_no', $requestData)
             && array_key_exists('address_source', $requestData)
            && array_key_exists('location', $requestData)
            && array_key_exists('locality', $requestData['location'])
            && array_key_exists('address', $requestData['location'])
            && array_key_exists('address2', $requestData['location'])
            && array_key_exists('pincode', $requestData['location'])
            && array_key_exists('city', $requestData['location'])
            && array_key_exists('state', $requestData['location'])
            && array_key_exists('country', $requestData['location'])
            
        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

       $fields = [
            'id' => $requestData['id']
        ];
        $rules = [
            'id' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       $data=   $this->imageUpload($requestData['profile_pic']);
			 if($requestData["address_source"]==1)
						   {//gps
                    $rec_addressgps=$requestData["location"]["address"];
						 $latitude = $requestData["location"]['latitude'];
                  $longtitude = $requestData["location"]['longitude'];  
						   }
				
				if($requestData["address_source"]==2)//manual
				{
				   $rec_address=$requestData["location"]["city"].
                        " ".$requestData["location"]["state"].
                        " ".$requestData["location"]["country"].
                        " ".$requestData["location"]["pincode"];
		$latLng=$this->get_lat_long($rec_address);
		 $latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
		$matchThese = ['id' => $requestData['id']];
		//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
		//User Search ID
		$user_searchid= array_key_exists('user_searchid',$requestData)?$requestData['user_searchid']:"";
		
		$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
		
			if ($status  != 0) {
	        $username = $requestData['contact_person'];
            $email = $requestData['email'];
            $profilePic = $data['name'];
		   // $gender = $requestData['gender'];
            //$age = $requestData['age'];
            $lat = $latitude;
            $long = $longtitude;
            $addressSource = $requestData['address_source'];
            $address = $requestData['location']['address'];
            $address2 = $requestData['location']['address2'];
            $locality = $requestData['location']['locality'];
            $city = $requestData['location']['city'];
            $state = $requestData['location']['state'];
            $country = $requestData['location']['country'];
            $pincode = $requestData['location']['pincode'];
			$profession = $requestData['profession'];
            $workPlace = $requestData['org_name'];
			$aboutMe = $requestData['about_me'];
           
			if($othersprofession=="")
				{
				$list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				
				}
				else
				{
				$getcategory=$requestData['others'];
				 $list=$this->multi_implode($requestData['profession'],",");
				
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$requestData['others'];
				}		
			 }
				$category_json=serialize($profession);
				}	
            $current_rec = CM4UserProfile::find($user[0]['id']);
         
			$current_rec->user_name = $username;
            $current_rec->email = $email;
            $current_rec->contact_person = $username;
            if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
           // $current_rec->gender = $gender;
            //$current_rec->age = $age;
            $current_rec->latitude = $lat;
            $current_rec->longitude = $long;
            $current_rec->address_source = $addressSource;
            $current_rec->address = $address."|".$address2;
            $current_rec->city = $city;
            $current_rec->state = $state;
            $current_rec->country = $country;
            $current_rec->locality = $locality;
            $current_rec->profile_status = 1;
            $current_rec->category = $getcategory;
            $current_rec->category_ids =$list ;
            $current_rec->category_json =$category_json ;
            $current_rec->user_name = $workPlace;
			$current_rec->about_us = $aboutMe;
            $current_rec->pincode = $pincode;
            $current_rec->update_profile_status = 1;
            $current_rec->isConsultat = 1;
			
			//CHECK FOR PREMIUM USER
				
			 $current_pre = CM4PremiumUser::find($user[0]['id']);
			if(count($current_pre)>0)
			{
			$current_pre->user_name = $username;
            $current_pre->email = $email;
            $current_pre->contact_person = $username;
            if($profilePic!="") {
                $current_pre->profile_pic = $profilePic;
            }
            $current_pre->latitude = $lat;
            $current_pre->longitude = $long;
            $current_pre->address_source = $addressSource;
            $current_pre->address = $address."|".$address2;
            $current_pre->city = $city;
            $current_pre->state = $state;
            $current_pre->country = $country;
            $current_pre->locality = $locality;
            $current_pre->profile_status = 1;
            $current_pre->category = $getcategory;
            $current_pre->category_ids =$list ;
            $current_pre->category_json =$category_json ;
            $current_pre->user_name = $workPlace;
			$current_pre->about_us = $aboutMe;
            $current_pre->pincode = $pincode;
            $current_pre->update_profile_status = 1;
			$current_pre->save();
			$solrupdate=$this->_update_premium_solr($requestData['id']);
			}
			
			
			//Add Social Info to another table 
			$youtube_link=$requestData['youtube_link'];
			$facebook_link=$requestData['facebook_link'];
			$twitter_link=$requestData['twitter_link'];
			$instagram_link=$requestData['instagram_link'];
			$snapchat_link=$requestData['snapchat_link'];
			$blog_link=$requestData['blog_link'];
			$msg_bf_call=$requestData['msg_bf_call'];
			$more_about=$requestData['more_about'];
			
			$matchTheseSocial = ['uid' => $requestData['id']];
			$usersocial = CM4UserSocial::where($matchTheseSocial)->get(['uid']);
            $chkstatus = $usersocial->count();
			if($chkstatus=='0')
			{
			$socialdata = [
                     "uid"=> $requestData['id'],
					 "youtube_link"=>$youtube_link,
                     "facebook_link"=>$facebook_link,
                     "twitter_link" =>$twitter_link,
                     "instagram_link" =>$instagram_link,
                     "snapchat_link" =>$snapchat_link,
                     "blog_link" =>$blog_link,
                     "msg_bf_call" => $msg_bf_call,
					 "more_about"=>$more_about
                     ];
				 CM4UserSocial::create($socialdata);
			}
			else
			{
			
			$socialdata = [
                     "youtube_link"=>$youtube_link,
                     "facebook_link"=>$facebook_link,
                     "twitter_link" =>$twitter_link,
                     "instagram_link" =>$instagram_link,
                     "snapchat_link" =>$snapchat_link,
                     "blog_link" =>$blog_link,
                     "msg_bf_call" => $msg_bf_call,
					 "more_about"=>$more_about
                     ];
				 CM4UserSocial::where($matchTheseSocial)->update($socialdata);	
			}
		if ($current_rec->save()) {
               $userid=$user[0]['id'];
			  
			   $solrupdate=$this->_update_by_username_solr($requestData['id']);
				$matchThese = ['id' => $requestData['id']];
				$user = CM4UserProfile::where($matchThese)->get();
                //return $user;
               $searched_uid=$userid;
		       $searched_contact=$requestData['contact_no'];	
		       $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
			else
			{
			$reviewcount='0';
			$avgrating='0';	
			}
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcountquery)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
			
				$address =explode('|',$user[0]['address']);
                $finalData=[
                    'id'=>$user[0]['id'],
                    'user_id'=>$user[0]['user_id'],
                    'user_name'=>$user[0]['user_name'],
					'user_searchid'=>$user[0]['user_searchid'],
                    'org_name'=>$user[0]['user_name'],
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'],
                    'gender'=>$user[0]['gender'],
                    'locality'=>$user[0]['locality'],
                    'address'=>$address[0],
                    'address2'=>$address[1],
                    'country'=>$user[0]['country'],
                    'city'=>$user[0]['city'],
                    'state'=>$user[0]['state'],
                    'latitude'=>$user[0]['latitude'],
                    'longitude'=>$user[0]['longitude'],
                    'call_time'=>$user[0]['call_time'],
                    'email'=>$user[0]['email'],
					'piggy_bal'  =>$user[0]['piggy_bal'],
                    'update_profile_status'  =>$user[0]['update_profile_status'],
                    'live_status'  =>$user[0]['live_status'],
                    'category_ids'  =>$user[0]['category_ids'],
                    'profile_status'  =>$user[0]['profile_status'],
                    'verification_status'  =>$user[0]['verification_status'],
                    'referal_code'  =>$user[0]['referal_code'],
                    'marital_status'  =>$user[0]['marital_status'],
                    'cc_fdail'  =>$user[0]['cc_fdail'],
                    'about_us'=>$user[0]['about_us'],
                    'user_rating'=>'',
                    'contact_person'=>$user[0]['contact_person'],
                    'contact_no'=>$user[0]['contact_no'],
                    'pincode'=>$user[0]['pincode'],
                    'service'=>$user[0]['category'],
					'per_min_val'=>$user[0]['per_min_val'],
                    'service_ids'=>unserialize($user[0]['category_json']),
                    'address_source'=>$user[0]['address_source'],
					'callcount'=>$callcount,
					'reviewcount'=>$reviewcount,
					'avgrating'=>$reviewcount,
					'youtube_link'=>$youtube_link,
					'facebook_link'=>$facebook_link,
					'twitter_link'=>$twitter_link,
					'instagram_link'=>$instagram_link,
					'snapchat_link'=>$snapchat_link,
					'blog_link'=>$blog_link,
					'msg_bf_call'=>$msg_bf_call,
					'more_about'=>$more_about
					];
			
			
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finalData, "device_key" => $token]);
            } else {
					$result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }
	
	
	
	  public function updateUserProfile_ios() {
       
	   $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();
		$created_date = date('Y-m-d : H:i:s');

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }
			
		if (!(array_key_exists('contact_person', $requestData)
            && array_key_exists('profile_pic', $requestData)
            && array_key_exists('id', $requestData)
            && array_key_exists('profession', $requestData)
            //&& array_key_exists('org_name', $requestData)
            //&& array_key_exists('age', $requestData)
            && array_key_exists('gender', $requestData)
            //&& array_key_exists('about_me', $requestData)
            && array_key_exists('contact_no', $requestData)
            //&& array_key_exists('start_time', $requestData)
            //&& array_key_exists('end_time', $requestData)
            //&& array_key_exists('email', $requestData)
            && array_key_exists('address_source', $requestData)
            && array_key_exists('location', $requestData)
            && array_key_exists('locality', $requestData['location'])
            && array_key_exists('address', $requestData['location'])
            && array_key_exists('address2', $requestData['location'])
            && array_key_exists('pincode', $requestData['location'])
            && array_key_exists('city', $requestData['location'])
            && array_key_exists('state', $requestData['location'])
            && array_key_exists('country', $requestData['location'])
            //&& array_key_exists('latitude', $requestData['location'])
            //&& array_key_exists('longitude', $requestData['location'])
        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
	
		
       $fields = [
            'id' => $requestData['id']
        ];
        $rules = [
            'id' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       $data=   $this->imageUpload($requestData['profile_pic']);
			 if($requestData["address_source"]==1)
						   {//gps
                    $rec_addressgps=$requestData["location"]["address"];
						 $latitude = $requestData["location"]['latitude'];
                  $longtitude = $requestData["location"]['longitude'];  
						   }
				
				if($requestData["address_source"]==2)//manual
				{
				   $rec_address=$requestData["location"]["city"].
                        " ".$requestData["location"]["state"].
                        " ".$requestData["location"]["country"].
                        " ".$requestData["location"]["pincode"];
		$latLng=$this->get_lat_long($rec_address);
		 $latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
		$matchThese = ['id' => $requestData['id']];
		//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
		//User Search ID
		$user_searchid= array_key_exists('user_searchid',$requestData)?$requestData['user_searchid']:"";
		
		$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
			if ( $status  != 0) {
	$username = $requestData['contact_person'];
            $email = $requestData['email'];
            $profilePic = $data['name'];
            $gender = $requestData['gender'];
            $age = $requestData['age'];
            $lat = $latitude;
            $long = $longtitude;
            $addressSource = $requestData['address_source'];
            $address = $requestData['location']['address'];
            $address2 = $requestData['location']['address2'];
            $locality = $requestData['location']['locality'];
            $city = $requestData['location']['city'];
            $state = $requestData['location']['state'];
            $country = $requestData['location']['country'];
            $pincode = $requestData['location']['pincode'];
			$profession = $requestData['profession'];
            $workPlace = $requestData['org_name'];
			$aboutMe = $requestData['about_me'];
            $call_time = $requestData['start_time']."|".$requestData['end_time'];
			//$per_min_val=$requestData['per_min_val'];
			$per_min_val=array_key_exists('per_min_val',$requestData)?$requestData['per_min_val']:"0.0";
			
			if($othersprofession=="")
				{
				$list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				}
				else
				{
				$getcategory=$requestData['others'];
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$requestData['others'];
				}		
			 }
				$category_json=serialize($profession);
				}

				//CHECK FOR PREMIUM USER
				
			 $current_pre = CM4PremiumUser::find($user[0]['id']);
			if(count($current_pre)>0)
			{
			$current_pre->user_name = $username;
            $current_pre->email = $email;
            $current_pre->contact_person = $username;
            if($profilePic!="") {
                $current_pre->profile_pic = $profilePic;
            }
            $current_pre->latitude = $lat;
            $current_pre->longitude = $long;
            $current_pre->address_source = $addressSource;
            $current_pre->address = $address."|".$address2;
            $current_pre->city = $city;
            $current_pre->state = $state;
            $current_pre->country = $country;
            $current_pre->locality = $locality;
            $current_pre->profile_status = 1;
            $current_pre->category = $getcategory;
            $current_pre->category_ids =$list ;
            $current_pre->category_json =$category_json ;
            $current_pre->user_name = $workPlace;
			$current_pre->about_us = $aboutMe;
            $current_pre->pincode = $pincode;
            $current_pre->update_profile_status = 1;
			$current_pre->save();
			$solrupdate=$this->_update_premium_solr($requestData['id']);
			}
			//END CHECK FOR PREMIUM USER

				
            $current_rec = CM4UserProfile::find($user[0]['id']);
            $current_rec->user_name = $username;
            $current_rec->email = $email;
            $current_rec->contact_person = $username;
            if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
            $current_rec->gender = $gender;
            $current_rec->age = $age;
            $current_rec->latitude = $lat;
            $current_rec->longitude = $long;
            $current_rec->address_source = $addressSource;
            $current_rec->address = $address."|".$address2;
            $current_rec->city = $city;
            $current_rec->state = $state;
            $current_rec->country = $country;
            $current_rec->locality = $locality;
            $current_rec->profile_status = 1;
            $current_rec->category = $getcategory;
            $current_rec->category_ids =$list ;
            $current_rec->category_json =$category_json ;
            $current_rec->user_name = $workPlace;
			$current_rec->about_us = $aboutMe;
            $current_rec->call_time = $call_time;
            $current_rec->pincode = $pincode;
            $current_rec->update_profile_status = 1;
			$current_rec->per_min_val=$per_min_val;
			$current_rec->user_searchid=$user_searchid;
			
			if ($current_rec->save()) {
               $userid=$user[0]['id'];
			   //Update Blogger Amt
			   \ DB::statement("update cm4_bloggers set per_min_val= $per_min_val where uid=$userid");	
			   
			   $solrupdate=$this->_update_by_username_solr($requestData['id']);
				$matchThese = ['id' => $requestData['id']];
				$user = CM4UserProfile::where($matchThese)->get();
                //return $user;
                $time =explode('|',$user[0]['call_time']);
                $address =explode('|',$user[0]['address']);
                $finalData=[
                    'id'=>$user[0]['id'],
                    'user_id'=>$user[0]['user_id'],
                    'user_name'=>$user[0]['user_name'],
					'user_searchid'=>$user[0]['user_searchid'],
                    'org_name'=>$user[0]['user_name'],
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'],
                    'gender'=>$user[0]['gender'],
                    'locality'=>$user[0]['locality'],
                    'age'=>$user[0]['age'],
                    'address'=>$address[0],
                    'address2'=>$address[1],
                    'country'=>$user[0]['country'],
                    'city'=>$user[0]['city'],
                    'state'=>$user[0]['state'],
                    'latitude'=>$user[0]['latitude'],
                    'longitude'=>$user[0]['longitude'],
                    'start_time'=>$time[0],
                    'end_time'=>$time[1],
					'email'=>$user[0]['email'],
					'piggy_bal'  =>$user[0]['piggy_bal'],
                    'update_profile_status'  =>$user[0]['update_profile_status'],
                    'file_type'  =>'0',
                    'updated_at'  =>$user[0]['updated_at'],
                    'created_at'  =>$user[0]['created_at'],
                    'live_status'  =>$user[0]['live_status'],
                    'data_source'  =>$user[0]['data_source'],
                    'category_ids'  =>$user[0]['category_ids'],
                    'data_source'  =>$user[0]['data_source'],
                    'user_rating'  =>$user[0]['user_rating'],
                    'profile_status'  =>$user[0]['profile_status'],
                    'device_id'  =>$user[0]['device_id'],
                    'verification_status'  =>$user[0]['verification_status'],
                    'referal_code'  =>$user[0]['referal_code'],
                    'marital_status'  =>$user[0]['marital_status'],
                    'call_time'  =>$time[0]."|".$time[1],
                    'cc_fdail'  =>$user[0]['cc_fdail'],
                    'cc_password'  =>$user[0]['cc_password'],
                    'about_us'=>$user[0]['about_us'],
                    'user_rating'=>'',
                    'contact_person'=>$user[0]['contact_person'],
                    'contact_no'=>$user[0]['contact_no'],
                    'pincode'=>$user[0]['pincode'],
                    'service'=>$user[0]['category'],
					'per_min_val'=>$user[0]['per_min_val'],
                    'service_ids'=>unserialize($user[0]['category_json']),
                    'address_source'=>$user[0]['address_source']
					];
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finalData, "device_key" => $token]);
            } else {
					$result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }
	
	
	/**
     * To Add money in my piggy Bank.
     *
     * @return Response
     */
    public function addMoneyPiggybank() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('uid',$requestData)&& array_key_exists('promocode', $requestData)
		)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
			}
        if (count($requestData) != 3) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'contact_no' => $requestData['contact_no'],
            'uid' => $requestData['uid'],
			'promocode' => $requestData['promocode']
        ];
        $rules = [
            'contact_no' => 'required',
            'uid' => 'required',
			'promocode' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
		$uid=$requestData['uid'];
		$contact_no=$requestData['contact_no'];
		$matchThese = ['uid' => $requestData['uid']];
		$user = CM4Promocodes::where($matchThese)->get();
        $status = $user->count(); 
		if($user->count()==0) {
		$promocode = $requestData['promocode'];
		$current_rec = CM4UserProfile::find($requestData['uid']);
        $piggybal=$current_rec->piggy_bal;
				if($promocode=='CM100')
				{
				$piggybal=$piggybal+100;	
				}	
				$current_rec->piggy_bal=$piggybal;
				$current_rec->save();
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$uid];	
		  $user = CM4PiggyBankAccount::where($check_user)->get(['id']);
			if(count($user)==0)
			{
		//Create User Piggy Ac
		 $piggybankdata = [
                     "user_name"=>$current_rec->contact_person,
                     "contact_no"=>$contact_no,
                     "address" => '',
                     "uid" =>$uid,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
					 "amt_earned"=>$piggybal
                     ];
            CM4PiggyBankAccount::create($piggybankdata);	
			}
		else
		{
	CM4PiggyBankAccount::where('uid',$uid)->update(['amt_earned' =>$piggybal]);
		}
		$cm4_promocodes=['uid' =>$uid,'contact_no'=>$contact_no,'promo_code'=>$promocode,'promo_amt'=>'100.00'];
		CM4Promocodes::create($cm4_promocodes);
			$user=array('piggy_bank'=>$piggybal);
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
		}
		else
		{
		 $result = collect(["status" => "0", "message" => 'You have already consumed this promocode.','errorCode'=>'160','errorDesc'=>'', "device_key" => $token]);
		}
	return response()->json($result,200);
	}
  
	/**
     * Paytm Amount Request.
     *
     * @return Response
     */
  /*  public function paytmAmtReq() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('uid',$requestData)&& array_key_exists('amt', $requestData)&& array_key_exists('contact_person', $requestData)
		)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
			}
        if (count($requestData) != 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
       
		$uid=trim($requestData['uid']);
		$contact_no=trim($requestData['contact_no']);
		$contact_person=trim($requestData['contact_person']);
		$reqamt=trim($requestData['amt']);
		$matchThese =['uid'=>trim($requestData['uid'])];
		
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$ccpiggybal=$CreditInfo[0]->piggy_bal;
		
		 $user = cm4PaytmRequest::where($matchThese)->get();
        $status = $user->count(); 
		$current_rec = CM4UserProfile::find(trim($requestData['uid']));
        $piggybal=$current_rec->piggy_bal;
				$updatepiggybal=0;
				if($reqamt<=$ccpiggybal && $reqamt<=50)	
				{
				$updatepiggybal=$ccpiggybal-$reqamt;	
				$current_rec->piggy_bal=$updatepiggybal;
				$current_rec->save();
		
		
		 //update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit - $reqamt where phone='".$contact_no."'");
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$uid];	
		  $user = CM4PiggyBankAccount::where($check_user)->get(['id']);
			if(count($user)>0)
			{
		\ DB::statement("update piggy_bank_ac set total_withdraw=total_withdraw + $reqamt where uid=$uid");	
			}
		
			 $data = [
                "uid" => $uid,
                "request_amt" =>$reqamt,
                "previous_bal" =>$piggybal,
                "avail_bal" =>$updatepiggybal
			];
            CM4PiggyBankTransaction::create($data);
			
			$data = [
                "uid" => $uid,
                "contact_person"=>$contact_person,
                "contact_no" =>$contact_no,
                "avail_bal" =>$updatepiggybal,
                "paytm_amt_req" => $reqamt,
				"reference_id"=>''
			];
            
			cm4PaytmRequest::create($data);
			$url="https://www.callme4.com:8443/uploaded_file/notify_pic/Notification_latest.png";
			$msg=array('page_index'=>5,'message'=>"Hi,$contact_person Paytm Balance will be updated.",'datetime'=>date('Y-m-d H:i:s'),'search_text'=>'','title'=>'Callme4 Request Confirmation.','url'=>$url); 
			$this->send_notification($contact_no,$msg);
			$user=array('piggy_bank'=>$updatepiggybal);
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
		}
		else
		{
		 $result = collect(["status" => "0", "message" => 'Callme4 has upgraded its services.The pay per view ads and Referal scheme is no longer available.Please update your app and visit play store to learn more about our new services.','errorCode'=>'160','errorDesc'=>'', "device_key" => $token]);
		}
	return response()->json($result,200);
	}*/
	
	
	public function paytmAmtReq() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('uid',$requestData)&& array_key_exists('amt', $requestData)&& array_key_exists('contact_person', $requestData)
		)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
			}
        if (count($requestData) != 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
       
		$uid=trim($requestData['uid']);
		$length=strlen($requestData['contact_no']);
		if($length==12){
			$contact_no=trim($requestData['contact_no']);
		}else{
			$contact_no=trim('91'.$requestData['contact_no']);
		}
		
		$contact_person=trim($requestData['contact_person']);
		$reqamt=trim($requestData['amt']);
		$matchThese =['uid'=>trim($requestData['uid'])];
		
		$qry="SELECT cast(credit as decimal(15,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$ccpiggybal=$CreditInfo[0]->piggy_bal;
		
		/* $user = cm4PaytmRequest::where($matchThese)->get();
        $status = $user->count();  */
		$current_rec = CM4UserProfile::find(trim($requestData['uid']));
        $piggybal=$current_rec->piggy_bal;
				$updatepiggybal=0;
				if($reqamt<=$ccpiggybal && $reqamt>=50)	
				{
				$data = [
                "uid" => $uid,
                "contact_person"=>$contact_person,
                "contact_no" =>$contact_no,
                "avail_bal" =>$updatepiggybal,
                "paytm_amt_req" => $reqamt,
				"reference_id"=>''
			];
            
			$insertpaytmreq=cm4PaytmRequest::create($data);
				if($insertpaytmreq->id)
				{
				$updatepiggybal=$ccpiggybal-$reqamt;	
				$current_rec->piggy_bal=$updatepiggybal;
				$current_rec->save();
		
		 //update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit - $reqamt where phone='".$contact_no."'");
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$uid];	
		  $user = CM4PiggyBankAccount::where($check_user)->get(['id']);
			if(count($user)>0)
			{
		\ DB::statement("update piggy_bank_ac set total_withdraw=total_withdraw + $reqamt where uid=$uid");	
			}
		
			/*  $data = [
                "uid" => $uid,
                "request_amt" =>$reqamt,
                "previous_bal" =>$piggybal,
                "avail_bal" =>$updatepiggybal
			];
            CM4PiggyBankTransaction::create($data); */
			
			/* $data = [
                "uid" => $uid,
                "contact_person"=>$contact_person,
                "contact_no" =>$contact_no,
                "avail_bal" =>$updatepiggybal,
                "paytm_amt_req" => $reqamt,
				"reference_id"=>''
			];
            
			cm4PaytmRequest::create($data); */
			$url="https://www.callme4.com:8443/uploaded_file/notify_pic/Notification_latest.png";
			$msg=array('page_index'=>5,'message'=>"Hi,$contact_person Paytm Balance will be updated.",'datetime'=>date('Y-m-d H:i:s'),'search_text'=>'','title'=>'Callme4 Request Confirmation.','url'=>$url); 
			$this->send_notification($contact_no,$msg);
			$user=array('piggy_bank'=>$updatepiggybal);
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
			}
			else
			{
			 $result = collect(["status" => "0", "message" => 'Sorry we are not able to process your Request.','errorCode'=>'160','errorDesc'=>'', "device_key" => $token]);	
			}
		
		}
		else
		{
		 $result = collect(["status" => "0", "message" => 'Request amount is more than your piggy Balance.','errorCode'=>'160','errorDesc'=>'', "device_key" => $token]);
		}
	return response()->json($result,200);
	}
	
	/**
     * paytmBloggerReq Amount Request.
     *
     * @return Response
     */
    public function paytmBloggerReq() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('uid',$requestData)&& array_key_exists('amt', $requestData)&& array_key_exists('contact_person', $requestData)
		)) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
			}
        if (count($requestData) != 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
       
		$uid=trim($requestData['uid']);
		$contact_no=trim($requestData['contact_no']);
		$contact_person=trim($requestData['contact_person']);
		$reqamt=trim($requestData['amt']);
		$matchThese =['uid'=>trim($requestData['uid'])];
		
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$ccpiggybal=$CreditInfo[0]->piggy_bal;
		
		/* $user = cm4PaytmRequest::where($matchThese)->get();
        $status = $user->count();  */
		$current_rec = CM4UserProfile::find(trim($requestData['uid']));
        $piggybal=$current_rec->piggy_bal;
				$updatepiggybal=0;
				if($reqamt<=$ccpiggybal && $reqamt>=50)	
				{
				$updatepiggybal=$ccpiggybal-$reqamt;	
				$current_rec->piggy_bal=$updatepiggybal;
				$current_rec->save();
		
		 //update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit - $reqamt where phone='".$contact_no."'");
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$uid];	
		  $user = CM4PiggyBankAccount::where($check_user)->get(['id']);
			if(count($user)>0)
			{
		\ DB::statement("update piggy_bank_ac set total_withdraw=total_withdraw + $reqamt where uid=$uid");	
			}
		
			 /* $data = [
                "uid" => $uid,
                "request_amt" =>$reqamt,
                "previous_bal" =>$piggybal,
                "avail_bal" =>$updatepiggybal
			];
            CM4PiggyBankTransaction::create($data); */
			
			$data = [
                "uid" => $uid,
                "contact_person"=>$contact_person,
                "contact_no" =>$contact_no,
                "avail_bal" =>$updatepiggybal,
                "paytm_amt_req" => $reqamt,
				"reference_id"=>''
			];
            
			cm4PaytmRequest::create($data);
			$url="https://www.callme4.com:8443/uploaded_file/notify_pic/Notification_latest.png";
			$msg=array('page_index'=>5,'message'=>"Hi,$contact_person Paytm Balance will be updated.",'datetime'=>date('Y-m-d H:i:s'),'search_text'=>'','title'=>'Callme4 Request Confirmation.','url'=>$url); 
			$this->send_notification($contact_no,$msg);
			$user=array('piggy_bank'=>$updatepiggybal);
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
		}
		else
		{
		 $result = collect(["status" => "0", "message" => 'Request amount is more than your piggy Balance.','errorCode'=>'160','errorDesc'=>'', "device_key" => $token]);
		}
	return response()->json($result,200);
	}
	
	
	/**
     * To update the user Device Info.
     *
     * @return Response
     */
    public function updateDeviceId() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('phone', $requestData)
            && array_key_exists('device_id', $requestData)

        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'phone' => $requestData['phone'],
            'device_id' => $requestData['device_id']
        ];
        $rules = [
            'phone' => 'required',
            'device_id' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
 $matchThese = ['contact_no' => $requestData['phone']];
	$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
 if ( $user->count()  != 0) {


            $deviceId = $requestData['device_id'];

            $current_rec = CM4UserProfile::find($user[0]['id']);
            $current_rec->device_id = $deviceId;


            if ($current_rec->save()) {


                $user[0]['id']=$user[0]['id'];
                $user[0]['device_id']=$deviceId;
                $user[0]['org_name']=$user[0]['user_name'];
                $user[0]['location']=['locality'=> $user[0]['locality']==NULL?"":$user[0]['locality'],
                    'address'=> $user[0]['address']==NULL?"":$user[0]['address'],
                    'city'=> $user[0]['city']==NULL?"":$user[0]['city'],
                    'state'=> $user[0]['state']==NULL?"":$user[0]['state'],
                    'country'=> $user[0]['country']==NULL?"":$user[0]['country'],
                    'latitude'=> $user[0]['lat']==NULL?"":$user[0]['lat'],
                    'longitude'=> $user[0]['long']==NULL?"":$user[0]['long']
                ];
                unset($user[0]['user_name']);
                unset($user[0]['locality']);
                unset($user[0]['address']);
                unset($user[0]['city']);
                unset($user[0]['state']);
                unset($user[0]['country']);
                unset($user[0]['lat']);
                unset($user[0]['long']);

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
            } else {

                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }


    /**
     * To update the user Name.
     *
     * @return Response
     */
    /* public function updateName() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";


        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('phone', $requestData)
            && array_key_exists('name', $requestData)
            && array_key_exists('referal_code', $requestData)

        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            //  $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'phone' => $requestData['phone'],
            'name' => $requestData['name']
        ];
        $rules = [
            'phone' => 'required',
            'name' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }


        $matchThese = ['contact_no' => $requestData['phone']];

        $user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();

        if ( $user->count()  != 0) {


            $name = $requestData['name'];
            $referalCode = $requestData['referal_code'];

            $current_rec = CM4UserProfile::find($user[0]['id']);
            $current_rec->contact_person = $name;
            $current_rec->referal_code = '';


            if ($current_rec->save()) {
                if($user[0]['profile_pic']!='') {
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                }else{
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }

                $user[0]['contact_person']=$name;
                $user[0]['id']=$user[0]['id'];
                $user[0]['service_ids']=[];
                $user[0]['org_name']=$user[0]['user_name'];
                $user[0]['location']=['locality'=> $user[0]['locality']==NULL?"":$user[0]['locality'],
                    'address'=> $user[0]['address']==NULL?"":$user[0]['address'],
                    'city'=> $user[0]['city']==NULL?"":$user[0]['city'],
                    'state'=> $user[0]['state']==NULL?"":$user[0]['state'],
                    'country'=> $user[0]['country']==NULL?"":$user[0]['country'],
                    'latitude'=> $user[0]['lat']==NULL?"":$user[0]['lat'],
                    'longitude'=> $user[0]['long']==NULL?"":$user[0]['long']
                ];
                unset($user[0]['user_name']);
                unset($user[0]['locality']);
                unset($user[0]['address']);
                unset($user[0]['city']);
                unset($user[0]['state']);
                unset($user[0]['country']);
                unset($user[0]['lat']);
                unset($user[0]['long']);

                //$user = CM4UserProfile::where($matchThese)->get();

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
            } else {
                $matchThese = ['contact_no' => $requestData['phone']];



              //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    } */
	
	/**
     * To update the user Name.
     *
     * @return Response
     */
  /*   public function updateName() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
   if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } 
		else {
            $requestData = Request::all();
			 }
        if (!(array_key_exists('phone', $requestData)
            && array_key_exists('name', $requestData)
            && array_key_exists('referal_code', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
           }
        $fields = [
            'phone' => $requestData['phone'],
            'name' => $requestData['name']
        ];
        $rules = [
            'phone' => 'required',
            'name' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
				];
			}
	$matchThese=['contact_no' => $requestData['phone']];
    $user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
        $refercodestatus=1;
		if ( $user->count()  != 0) {
			$name = $requestData['name'];
            $is_refered = $requestData['referal_code'];
			$current_rec = CM4UserProfile::find($user[0]->id);
            $current_rec->contact_person = $name;
		CM4PiggyBankAccount::where('uid',$user[0]->id)->update(['user_name' =>$name]);
			//Working
			$requesterID=$user[0]->id;
			if($is_refered!="")
			 {
			$matchreferal = ['refer_code' =>$is_refered];
            $referuser = CM4UserRefer::where($matchreferal)->get(['uid']);
			if(count($referuser)>0)
			{
			$refercodestatus=1;
			$countuser=\ DB::SELECT("SELECT uid,earned_by_uid from cm4_user_refers where refer_code='".$is_refered."' and not find_in_set($requesterID,earned_by_uid)");
			
			if(count($countuser)!=0)
			{
			if($countuser[0]->earned_by_uid=="")	
			{
			$referedamt="5.00";
			$uid=$countuser[0]->uid;
			\ DB::statement("UPDATE cm4_user_refers SET earned_by_uid ='".$user[0]->id."',earned_amt='".$referedamt."' where uid=$uid");
			\ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $referedamt where id=$uid");
			\ DB::statement("update piggy_bank_ac set amt_earned=amt_earned + $referedamt where uid=$uid");
			//\ DB::statement("update piggy_bank_transaction set avail_bal=avail_bal + $referedamt where uid=$uid");	
			}	
			else
				{
				$earnbyuidstr=$countuser[0]->earned_by_uid;	
				$earnbyuidarray=explode(",",$earnbyuidstr);
				$earnbyuidarray[]=$user[0]->id;
				$earnbyuidstrnew=implode(",",$earnbyuidarray);
				$referedamt="5.00";
				$uid=$countuser[0]->uid;
				\ DB::statement("UPDATE cm4_user_refers SET earned_by_uid ='".$earnbyuidstrnew."',earned_amt='".$referedamt."' where uid=$uid");
				\ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $referedamt where id=$uid");
				\ DB::statement("update piggy_bank_ac set amt_earned=amt_earned + $referedamt where uid=$uid");
				//\ DB::statement("update piggy_bank_transaction set avail_bal=avail_bal + $referedamt where uid=$uid");	
				}
			}	
			
			 }
			 else
			 {
			$refercodestatus=0;	 
			 }
		}
		  if ($current_rec->save()) {
                if($user[0]['profile_pic']!='') {
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                }else{
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }

                $user[0]['contact_person']=$name;
                $user[0]['id']=$user[0]['id'];
                $user[0]['service_ids']=[];
                $user[0]['org_name']=$user[0]['user_name'];
				$user[0]['refercodestatus']=$refercodestatus;
                $user[0]['location']=['locality'=> $user[0]['locality']==NULL?"":$user[0]['locality'],
                    'address'=> $user[0]['address']==NULL?"":$user[0]['address'],
                    'city'=> $user[0]['city']==NULL?"":$user[0]['city'],
                    'state'=> $user[0]['state']==NULL?"":$user[0]['state'],
                    'country'=> $user[0]['country']==NULL?"":$user[0]['country'],
                    'latitude'=> $user[0]['lat']==NULL?"":$user[0]['lat'],
                    'longitude'=> $user[0]['long']==NULL?"":$user[0]['long']
                ];
                unset($user[0]['user_name']);
                unset($user[0]['locality']);
                unset($user[0]['address']);
                unset($user[0]['city']);
                unset($user[0]['state']);
                unset($user[0]['country']);
                unset($user[0]['lat']);
                unset($user[0]['long']);

                //$user = CM4UserProfile::where($matchThese)->get();

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
            } else {
                $matchThese = ['contact_no' => $requestData['phone']];



              //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }
	 */
	/**
     * To update the user Name.
     *
     * @return Response
     */
    public function updateName() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
   if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } 
		else {
            $requestData = Request::all();
			 }
        if (!(array_key_exists('phone', $requestData)
            && array_key_exists('name', $requestData)
            && array_key_exists('referal_code', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
       
        $fields = [
            'phone' => $requestData['phone'],
            'name' => $requestData['name']
        ];
        $rules = [
            'phone' => 'required',
            'name' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
				];
			}
	$length=strlen($requestData['phone']);
	if($length==12){
		$mobilenonew=$requestData['phone'];
	}else{
		$mobilenonew='91'.$requestData['phone'];
	}
	$matchThese=['contact_no' => $mobilenonew];
    $mycontact=$mobilenonew;
	$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
        $refercodestatus=1;
		if ( $user->count()  != 0) {
			$name = $requestData['name'];
            //$is_refered = $requestData['referal_code'];
			$is_refered = "";
				//New Key added for User Seach ID
		$user_searchid= array_key_exists('user_searchid',$requestData)?$requestData['user_searchid']:"";
			$current_rec = CM4UserProfile::find($user[0]->id);
            $current_rec->contact_person =$name;
			$current_rec->user_searchid =$user_searchid;
		CM4PiggyBankAccount::where('uid',$user[0]->id)->update(['user_name' =>$name]);
			//Working
			$requesterID=$user[0]->id;
			if($is_refered!="")
			 {
			$countreferusers=\ DB::SELECT("SELECT id from cm4_user_refers where find_in_set($requesterID,earned_by_uid)");
			$userrefercount=count($countreferusers);
			if($userrefercount==0)
			{	
			$countuser=\ DB::SELECT("SELECT uid,earned_by_uid from cm4_user_refers where refer_code='".$is_refered."'");
			
			if(count($countuser)!=0)
			{
			$refercodestatus=1;
			if($countuser[0]->earned_by_uid=="")	
			{
			$referedamt="5.00";
			$uid=$countuser[0]->uid;
			
			 $matchThese = ['id'=>$uid];
		$userInfo = CM4UserProfile::where($matchThese)->get(['contact_no']);
		$contact_no=$userInfo[0]->contact_no;
			
			
			
			\ DB::statement("UPDATE cm4_user_refers SET earned_by_uid ='".$user[0]->id."',earned_amt='".$referedamt."' where uid=$uid");
			\ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $referedamt where id=$uid");
			
			//update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit + $referedamt where phone='".$contact_no."'");
			
			\ DB::statement("update piggy_bank_ac set amt_earned=amt_earned + $referedamt where uid=$uid");
			//\ DB::statement("update piggy_bank_transaction set avail_bal=avail_bal + $referedamt where uid=$uid");	
			}	
			else
				{
				$earnbyuidstr=$countuser[0]->earned_by_uid;	
				$earnbyuidarray=explode(",",$earnbyuidstr);
				$earnbyuidarray[]=$user[0]->id;
				$earnbyuidstrnew=implode(",",$earnbyuidarray);
				$referedamt="5.00";
				$uid=$countuser[0]->uid;
				 $matchThese = ['id'=>$uid];
		$userInfo = CM4UserProfile::where($matchThese)->get(['contact_no']);
		$contact_no=$userInfo[0]->contact_no;
				
				
				\ DB::statement("UPDATE cm4_user_refers SET earned_by_uid ='".$earnbyuidstrnew."',earned_amt='".$referedamt."' where uid=$uid");
				\ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $referedamt where id=$uid");
				//update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit + $referedamt where phone='".$contact_no."'");
				
				\ DB::statement("update piggy_bank_ac set amt_earned=amt_earned + $referedamt where uid=$uid");
				//\ DB::statement("update piggy_bank_transaction set avail_bal=avail_bal + $referedamt where uid=$uid");	
				}
			}
			else
			{
			$refercodestatus=0;	
			}		
			 }
			}
			 if ($current_rec->save()) {
                if($user[0]['profile_pic']!='') {
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                }else{
                    $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }

                $user[0]['contact_person']=$name;
                $user[0]['id']=$user[0]['id'];
                $user[0]['service_ids']=[];
                $user[0]['org_name']=$user[0]['user_name'];
				$user[0]['refercodestatus']=$refercodestatus;
                $user[0]['location']=['locality'=> $user[0]['locality']==NULL?"":$user[0]['locality'],
                    'address'=> $user[0]['address']==NULL?"":$user[0]['address'],
                    'city'=> $user[0]['city']==NULL?"":$user[0]['city'],
                    'state'=> $user[0]['state']==NULL?"":$user[0]['state'],
                    'country'=> $user[0]['country']==NULL?"":$user[0]['country'],
                    'latitude'=> $user[0]['lat']==NULL?"":$user[0]['lat'],
                    'longitude'=> $user[0]['long']==NULL?"":$user[0]['long']
                ];
                unset($user[0]['user_name']);
                unset($user[0]['locality']);
                unset($user[0]['address']);
                unset($user[0]['city']);
                unset($user[0]['state']);
                unset($user[0]['country']);
                unset($user[0]['lat']);
                unset($user[0]['long']);

                //$user = CM4UserProfile::where($matchThese)->get();

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$user, "device_key" => $token]);
            } else {
                $matchThese = ['contact_no' => $mobilenonew];



              //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);
		}
	return response()->json($result, 200);
	}
	
	//GET REFERAL Code
	/**
     * @return Response
     */
    public function getReferalCode() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
	if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
				];
			}
			$check_user=['uid' => $requestData['uid']];	
		  $user = CM4UserRefer::where($check_user)->get();
			//get facebook login..
		$fbuser = CM4FbUsers::where($check_user)->get(['id']);
        $fbstatus = $fbuser->count();
			if(count($user)==0)
			{
		$referal=$this->gen_referal_code();
		$cm4_user_refers=array('uid'=>$requestData['uid'],'refer_code'=>$referal,'earned_by_uid'=>'','earned_amt'=>'','created_at'=>date('Y-m-d H:i:s'));	  
			$datarefer=CM4UserRefer::create($cm4_user_refers);
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>array('refer_code'=>$referal,'referalamt'=>'5','newuseramt'=>'0','fblogin_status'=>$fbstatus),"device_key" => $token]);
            } 
			else {
   $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>array('refer_code'=>$user[0]->refer_code,'referalamt'=>'5','newuseramt'=>'0','fblogin_status'=>$fbstatus), "device_key" => $token]);
        }
return response()->json($result, 200);
    }
    
	
	//GET LAT LONG FROM ADDRESS
	public function get_lat_long($address)
    {
         $address = str_replace(" ", "+", $address);
        $region = "Delhi/NCR";
       $json =file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false&key=AIzaSyB8CigJhhX9B6jvdtJvWEX7QJILbF6xPks&region=$region");
		
		//echo "https://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false&key=AIzaSyB8CigJhhX9B6jvdtJvWEX7QJILbF6xPks&region=$region";die;
		//$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
        $json = json_decode($json);
		
		if(!empty($json->{'results'}[0]))
			{
		$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        $latlong=array('lat'=>$lat,'lng'=>$long);
        return $latlong;
			}
			else 
			{
			$latlong=array('lat'=>28.6139,'lng'=>77.2090);
			 return $latlong;
			}
    }


    public function imageUpload($data) {

        //$destinationPath = 'uploads/' ;
        $destinationPath = $_SERVER['DOCUMENT_ROOT']."/uploaded_file/user_pic" ;
        if (!is_dir($destinationPath))
        {
            mkdir($destinationPath, 0777, true);
        }

        $filename = time().'.jpg';

        $status= file_put_contents($destinationPath . '/' . $filename, base64_decode($data));
      //  echo  $destinationPath;
       // die;

        $data =['status'=>$status,'name'=>$filename];

        return $data;

    }


    //IMAGE UPLOAD IN LOOP
	 public function imagephonebook($data,$count) {

        //$destinationPath = 'uploads/' ;
        $destinationPath = $_SERVER['DOCUMENT_ROOT']."/uploaded_file/user_pic" ;
        if (!is_dir($destinationPath))
        {
            mkdir($destinationPath, 0777, true);
        }

        $filename = $count.time().'.jpg';

        $status= file_put_contents($destinationPath . '/' . $filename, base64_decode($data));
      //  echo  $destinationPath;
       // die;

        $data =['status'=>$status,'name'=>$filename];

        return $data;

    }
	
	
	
	
	
	
	
	
	/**
     * add people.
     *
     * @return Response
     */
    public function addPeople()
    {

        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        // $requestUser = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

          $totalRecord =count($requestData["data"]);
         $insertedRecord =0;
        if(count($requestData["data"])==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.401'),'errorCode'=>'401','errorDesc'=>\Config::get('constants.results.401'), "device_key" => $token]);
            return $result;
        }
        $flag=0;
        // $requestData["data"][0]['location'];
      //  $a= array_key_exists('city',$requestData["data"][0]['location']);
//return $requestData["data"][0];
        foreach ($requestData["data"] as $value) {
            //return $value;

            if (!(array_key_exists('contact_person', $value)
                && array_key_exists('profile_pic', $value)
                && array_key_exists('uploader_id', $value)
                && array_key_exists('profession', $value)
                && array_key_exists('org_name', $value)
                && array_key_exists('age', $value)
                && array_key_exists('gender', $value)
                && array_key_exists('about_me', $value)
                && array_key_exists('contact_no', $value)
                && array_key_exists('start_time', $value)
                && array_key_exists('end_time', $value)
                && array_key_exists('email', $value)
                && array_key_exists('address_source', $value)
                && array_key_exists('location', $value)
                && array_key_exists('locality', $value['location'])
                && array_key_exists('address', $value['location'])
                && array_key_exists('address2', $value['location'])
                && array_key_exists('pincode', $value['location'])
                && array_key_exists('city', $value['location'])
                && array_key_exists('state', $value['location'])
                && array_key_exists('country', $value['location'])
                && array_key_exists('latitude', $value['location'])
                && array_key_exists('longitude', $value['location'])
            )
            ) {
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
                // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
                return $result;
            }

           /*  if (count($value) != 14) {
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
                // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
                return $result;
            } */


            $fields = [
                'contact_person' => $value['contact_person'],
                'uploader_id' => $value['uploader_id'],
                'profession' => $value['profession'],
                'contact_no' => $value['contact_no'],
            ];
            $rules = [
                'contact_person' => 'required',
                'uploader_id' => 'required',
                'profession' => 'required',
                'contact_no' => 'required'
            ];
            $valid = \Validator::make($fields, $rules);
            if ($valid->fails()) {
                return [
                    'status' => '0',
                    'message' => 'validation_failed',
                    'errorCode' => '',
                    'errorDesc' => $valid->errors()
                ];
            }
            //  return $value;

            $matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4UserProfile::where($matchThese)->get();
            $Profilestatus = $user->count();
				
				//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$value)?$value['others']:"";
				//new code 
	$matchThese = ['id' => $value['uploader_id']];
			$uploader = CM4UserProfile::where($matchThese)->first();
          $uploader_name=$uploader['contact_person'];	  
		  $uploader_contact=$uploader['contact_no'];

            $matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4TempAppUserData::where($matchThese)->get();
            $userDatastatus = $user->count();

            if ($Profilestatus == 0 && $userDatastatus == 0) {
                $amt_earned=array_key_exists('amt_earned',$value)?$value['amt_earned']:'0';
				//new code for lat long
				 if($value["address_source"]==1)
						   {//gps
                    $rec_addressgps=$value["location"]["address"];
						 $latitude = $value["location"]['latitude'];
                  $longtitude = $value["location"]['longitude'];  
						   }
				
				if($value["address_source"]==2)//manual
				{
				   $rec_address=$value["location"]["city"].
                        " ".$value["location"]["state"].
                        " ".$value["location"]["country"].
                        " ".$value["location"]["pincode"];

        $latLng=$this->get_lat_long($rec_address);
			
		$latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
				
				$imagedata=   $this->imageUpload($value['profile_pic']);
                
				if($othersprofession=="")
				{
				 
				 $list=$this->multi_implode($value['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($value['profession']);
				}
				else
				{
				$getcategory=array_key_exists('others',$value)?$value['others']:"";
				$list=0;
				$category_json="a:0:{}";
				}	
				
				//$list=$this->multi_implode($value['profession'],",");
                $address=$value['location']['address']."|".$value['location']['address2'];
                //$list = implode(',',array_unique(explode(',', $list)));
                $data = [
                    "contact_person" => $value["contact_person"],
                    "amt_earned" => $amt_earned,
					"profile_pic" => $imagedata["name"],
                    "uploader_id" => $value["uploader_id"],
                    "profession" =>  $getcategory,
                    "profession_ids" => $category_json,
                    "work_place" => $value["org_name"],
                    "age" => $value["age"],
                    "gender" => $value["gender"],
                    "about_me" => $value["about_me"],
                    "contact_no" => $value["contact_no"],
                    "start_time" => $value["start_time"],
                    "end_time" => $value["end_time"],
                    "email" => $value["email"],
                    "address_source" => $value["address_source"],
                    "locality" =>$value['location']['locality'],
                    "address" => $address,
                    "pincode" => $value['location']['pincode'],
                    "city" => $value['location']['city'],
                    "state" => $value['location']["state"],
                    "country" => $value['location']["country"],
                    "latitude" => $latitude,
                    "longitude" => $longtitude,
					"uploader_name"=>$uploader_name,
					"uploader_contact"=>$uploader_contact
                ];
               // return $data;
                CM4TempAppUserData::create($data);
                $flag=1;
                $insertedRecord++;

            }
        }
            if($flag==1){
                $data =["total_record"=>$totalRecord,"inserted_record"=>$insertedRecord];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'), 'errorCode' => '100', 'errorDesc' => \Config::get('constants.results.100'),"data"=>$data, "device_key" => $token]);

            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.103'), 'errorCode' => '103', 'errorDesc' => \Config::get('constants.results.103'), "device_key" => $token]);

            }

        return response()->json($result, 200);
    }

    /**
     * Show list of verified People.
     *
     * @return Response
     */
    public function verifiedPeople() {
        //\Log::info('Verified People list fetch.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }


        $matchThese = ['uploader_id' =>$requestData['uid'] ];
        $list=CM4TempAppUserData::where($matchThese)
            ->whereDate('created_at', '=', \Carbon\Carbon::today()
                ->toDateString())->get(['contact_person','profile_pic','profession','contact_no','work_place','amt_earned','status']);


        $list->each(function ($item) {

            if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }


        })->values();

        $yesterdayList=CM4TempAppUserData::where($matchThese)
                        ->whereDate('created_at', '<', \Carbon\Carbon::today()
                ->toDateString())->get(['contact_person','profile_pic','profession','contact_no','work_place','amt_earned','status']);

        $yesterdayList->each(function ($item) {

           /* if($item->profile_pic!='') {
                $item->profile_pic = "https://www.callme4.com/api/public/images/" . $item->profile_pic;
            }else{
                $item->profile_pic = "https://www.callme4.com/api/public/noImage.png";
            }*/

            if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }



        })->values();
        $data=["today"=>$list,"history"=>$yesterdayList];



      //  return $list;




        $status = $list->count();
        $yesterdayStatus = $yesterdayList->count();


        if ($status||$yesterdayStatus) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $data, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    /**
     * Show list of verified Added People.
     *
     * @return Response
     */
    public function verifiedAddedPeople() {
        //\Log::info('Verified People list fetch.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	if(!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
	
	$uid=$requestData['uid'];

	$matchThese = ['id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get(['contact_no']);
		$contact_no=$userInfo[0]->contact_no;
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		
	//$qry="SELECT cast(piggy_bal as decimal(6,2)) as piggy_bal FROM `cm4_user_profile` WHERE id='".$uid."'";
		//	$userInfo= \DB::select($qry);
	$piggybal=0;
	if(count($CreditInfo)=='1')
	{
	$piggybal=$CreditInfo[0]->piggy_bal;
	$matchThese = ['uploader_id' =>$requestData['uid'] ];
        $list=CM4TempAppUserData::where($matchThese)->orderBy('id','desc')->get(['contact_person','profile_pic','profession','contact_no','work_place','amt_earned','status','created_at']);
		$list->each(function ($item) {
			if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
		})->values();
		$status = $list->count();
        
		if ($status) {
            $data = collect(["status" => "1","piggy_bal"=>$piggybal,"message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "userlist"=>$list, "device_key" => $token]);
        } 
	
	else {
            $data = collect([ "status" => "1","piggy_bal"=>$piggybal,"message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"userlist"=>array(),"device_key" => $token]);
        }
	
	}
		else {
            $data = collect([ "status" => "0","piggy_bal"=>$piggybal,"message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"userlist"=>array(),"device_key" => $token]);
        }
	return response()->json($data, 200);
		}
	
	
	
	
	/**
     * Obtain the strings from ids.
     *
     * @return Response
     */
    public function getsearchtags_of_ids($tagsid)
    {


       // $string= CM4Categories::whereIn('category_id', explode(',',$tagsid))->get(['category_name','type_id']);
	$stringqry="SELECT category_name,type_id FROM cm4_categories WHERE category_id IN($tagsid) ORDER BY FIELD(category_id,$tagsid)";       
	$string= \ DB::select($stringqry);	
        $x=0;
        $str="";
        foreach($string as $key=>$value)
        {
            if($value->type_id=='1')
            {
                $x++;
                if($x==1)
                {
                    $str .= $value->category_name. ":";
                }
                else
                {
                    $str = trim($str, ',');
                    $str .= ";".$value->category_name. ":";
                }
            }
            else if($value->type_id=='2')
            {
                $str .= $value->category_name. ",";
            }
            else
            {
                $str .= $value->category_name. ",";
            }

        }
        $str = trim($str, ',');
        return $str;
    }

    function multi_implode($array, $glue) {
        $ret = '';
	    foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= $this->multi_implode($item,$glue) . $glue;
            } else {
                $ret .= $item . $glue;
            }
        }
		$ret = substr($ret, 0, 0-strlen($glue));
		return $ret;
    }

    /**
     * Show the form for fetching the User inforamtion.
     *
     * @return Response
     */
    public function getUserInfo() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();
        if (Request::header('content-type') == "application/json") {
            $user = Request::json()->all();
        } else {
            $user = Request::all();
        }

        if (!(array_key_exists('uid', $user))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($user) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        $fields = [
            'uid' => $user['uid']
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
        $records = CM4UserProfile::where('id', $user['uid'])->get();
        $count= $records->count();

        if ($count!=0) {

//return $records[0]['address'];
            if($records[0]['call_time']!="") {
                $time = explode('|', $records[0]['call_time']);
            }else{
                $time[0]="";
            }
            if($records[0]['address']!=""){
            $address =explode('|',$records[0]['address']);
            }else{
                $address[0]="";
            }
            $records[0]['id']=$records[0]['id'];

            $records[0]['org_name']=$records[0]['user_name'];
            //$records[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";
            if( $records[0]['profile_pic']!='') {
                $records[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" .  $records[0]['profile_pic'];
            }else{
                $records[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            $records[0]['address']=$address[0];
            $records[0]['address2']=isset($address[1])?$address[1]:"";
            $records[0]['start_time']=$time[0];
            $records[0]['end_time']=isset($time[1])?$time[1]:"";


            $records[0]['service']=$records[0]['category'];
            $records[0]['service_ids']=unserialize($records[0]['category_json']);

            unset($records[0]['category_json']);
            unset($records[0]['category']);
            $finalData=['user'=>$records];
           // $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $finalData["user"][0]], "device_key" => $token]);
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $finalData["user"][0], "device_key" => $token]);


        } else {

            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

           // $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100')], "device_key" => $token]);

        }
        return response()->json($data, 200);
    }

    /**
     * To add user account.
     *
     * @return Response
     */
    public function addAcount() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('bank_name', $requestData)
            && array_key_exists('account_number', $requestData)
            && array_key_exists('bank_ifsc_code', $requestData)

        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'uid' => $requestData['uid'],
            'bank_name' => $requestData['bank_name'],
            'account_number' => $requestData['account_number'],
            'bank_ifsc_code' => $requestData['bank_ifsc_code'],
        ];
        $rules = [
            'uid' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'bank_ifsc_code' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }


        $matchThese = ['id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get();
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $matchThese = ['uid' => $requestData['uid']];

        $user = CM4PiggyBankAccount::where($matchThese)->get();
        $status = $user->count();
//return $userInfo[0]->user_name;
        $username = $userInfo[0]->user_name;
        $contactNumber = $userInfo[0]->contact_no;
        $address = $userInfo[0]->address;
        $uid = $requestData['uid'];
        $bankName = $requestData['bank_name'];
        $bankIfseCode = $requestData['bank_ifsc_code'];
        $accountNumber = $requestData['account_number'];

        if ( $status  != 0) {

            $current_rec = CM4PiggyBankAccount::find($user[0]['id']);
            $current_rec->bank_name = $bankName;
            $current_rec->bank_ifsc_code = $bankIfseCode;
            $current_rec->account_number = $accountNumber;
            $current_rec->updated_at = $created_date;

            if ($current_rec->save()) {

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
            } else {

                //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $data = [
                     "user_name" => $username,
                     "contact_no" => $contactNumber,
                     "address" => $address,
                     "uid" => $uid,
                     "bank_name" =>$bankName,
                     "bank_ifsc_code" =>$bankIfseCode,
                     "account_number" => $accountNumber,
                     "updated_at" => $created_date,
                     "created_at" => $created_date
                     ];
            CM4PiggyBankAccount::create($data);

            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
        }

        return response()->json($result, 200);


    }

    /**
     * Show the form for fetching the booster packs.
     *
     * @return Response
     */

    public function boosterPacks() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('latitude', $requestData)
             &&array_key_exists('longitude', $requestData)
             &&array_key_exists('uid', $requestData)
        )) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       // return $requestData;
        $uid=$requestData['uid'];

        $rec = \DB::table('cm4_user_booster_mapping')
            ->select(\DB::raw("GROUP_CONCAT(bid) as 'ids'"))
            ->where('uid',$uid)
            ->get();

       $ids= $rec[0]->ids==null?0:$rec[0]->ids;

        $latitude=$requestData['latitude'];
        $longitude=$requestData['longitude'];
//latitude,longitude,
        $query="SELECT id, keywords AS 'name',required_result as 'record_number',amt_declared as 'amount',category_ids as service_id,booster_image,`desc`,boosterpacktype,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(latitude))* COS( RADIANS( longitude ) - RADIANS($longitude)) + SIN ( RADIANS($latitude) ) * SIN(RADIANS( latitude )))) AS distance FROM booster_packs where id NOT IN ($ids ) HAVING distance < 500 order by distance";

        $data= \ DB::select($query);

        foreach($data as $val){
            if($val->booster_image!="") {
                $val->booster_image = "https://www.callme4.com:8443/uploaded_file/booster_pic/" . $val->booster_image;
            }else{
                $val->booster_image = "https://www.callme4.com:8443/uploaded_file/booster_pic/noImage.png";
            }
            //$val->desc = "Dummy Dummy Dummy";
          /*if($val->images!='') {
                $val->images = "https://www.callme4.com/api/public/images/" . $val->images;
            }else{
                $val->images = "https://www.callme4.com/api/public/noImage.png";
            }*/
        }


        if (count($data)!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $data, "device_key" => $token]);
          //  $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $data], "device_key" => $token]);
        } else {
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            //$data = collect(["status" => [ "code" => "105", "message" => \Config::get('constants.results.105')], "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    /**
     * Make Withdraw Request .
     *
     * @return Response
     */
    public function withdrawRequest() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('amount', $requestData)

        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'uid' => $requestData['uid'],
            'amount' => $requestData['amount'],
        ];
        $rules = [
            'uid' => 'required',
            'amount' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }

        $uid =$requestData['uid'];
        $amount =$requestData['amount'];


        $user = CM4UserProfile::where('piggy_bal', '>=', $amount)->where('id', '=',$uid)->get(['piggy_bal']);
        $status = $user->count();
        if ($status == 0) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.115'), 'errorCode' => '115', 'errorDesc' => \Config::get('constants.results.115'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        $available_amount=$user[0]->piggy_bal;
       // return $available_amount;

            $data = [
                "uid" => $uid,
                "request_amt" => $amount,
                "previous_bal" => $available_amount,
                "avail_bal" => $available_amount-$amount,
                "request_date" => $created_date

            ];
            CM4PiggyBankTransaction::create($data);
            CM4UserProfile::where('id', $uid)->update(['piggy_bal' => $available_amount-$amount]);
            CM4PiggyBankAccount::where('uid',$uid)->increment('total_withdraw',$amount,['remaining_balance' => $available_amount-$amount]);
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);


        return response()->json($result, 200);


    }


	//user alive status
	   public function useralivestatus() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		$category_count_status=0;
        $created_date = \Carbon\Carbon::today();
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }
 if (!(array_key_exists('uid', $requestData)
            && array_key_exists('is_installed',$requestData)
		)
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'uid' => $requestData['uid'],
            'is_installed' => $requestData['is_installed'],
        ];
        $rules = [
            'uid' => 'required',
            'is_installed' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
		$uid =$requestData['uid'];
       $is_installed =$requestData['is_installed'];
        $updated_at=date('Y-m-d H:i:s');
	CM4UserProfile::where('id', $uid)->update(['is_installed' => $is_installed,'updated_at'=>$updated_at]);
	 $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),"category_count_status"=>$category_count_status,"device_key" => $token]);
	return response()->json($result, 200);
	}
	
	//send notification 
  function send_notification($uploader_contact,$msg)
  {
		 define( 'API_ACCESS_KEY', 'AIzaSyD0pskmntAY5Nm0uOrUasDDG7O1ZQTaPTk');
		$query="SELECT device_id  FROM CM4_user_info  WHERE phone ='".$uploader_contact."' order by id desc limit 0,1";
			$data= \ DB::select($query);
		if(count($data)>0)
		{
		$device_id=$data[0]->device_id;
	  $fields = array('registration_ids'=>array($device_id),'data'=>$msg); 
	  $headers =array('Authorization: key=' . API_ACCESS_KEY,'Content-Type: application/json');
					
					 $ch = curl_init();
					curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
					curl_setopt( $ch,CURLOPT_POST, true );
					curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
					curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
					$result = curl_exec($ch);
					// Execute post
			$result = curl_exec($ch);
			
					curl_close( $ch );
			//echo $result;die;
		return true;
		}
  }
	
	
	//Update user device id
	   public function updateuserdeviceid() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('device_id',$requestData)

        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
        }
       $fields = [
            'contact_no' => $requestData['contact_no'],
            'device_id' => $requestData['device_id'],
        ];
        $rules = [
            'contact_no' => 'required',
            'device_id' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        $length=strlen($requestData['contact_no']);
        if($length==12){
        	$phone =$requestData['contact_no'];
        }else{
        	$phone ='91'.$requestData['contact_no'];
        }
        
        $device_id =$requestData['device_id'];

            CM4UserInfo::where('phone', $phone)->update(['device_id' => $device_id]);
	$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
    return response()->json($result, 200);
    }
	
	
    
	
    /**
     * select booster pack by user.
     *
     * @return Response
     */
    public function userBoosterPack() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('bid', $requestData)

        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'uid' => $requestData['uid'],
            'bid' => $requestData['bid'],
        ];
        $rules = [
            'uid' => 'required',
            'bid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }

        $uid =$requestData['uid'];
        $bid =$requestData['bid'];



        $data = [
            "uid" => $uid,
            "bid" => $bid

        ];
        CM4UserBoosterMapping::create($data);
       // CM4UserProfile::where('id', $uid)->update(['piggy_bal' => $available_amount-$amount]);

        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);


        return response()->json($result, 200);


    }

    /**
     * select booster pack by user.
     *
     * @return Response
     */
    public function selectedboosterPacks() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
           'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        // return $requestData;
        $uid=$requestData['uid'];

        $rec = \DB::table('cm4_user_booster_mapping')
            ->select(\DB::raw("GROUP_CONCAT(bid) as 'ids'"))
            ->where('uid',$uid)
            ->get();

        $ids= $rec[0]->ids==null?0:$rec[0]->ids;

        $query="SELECT id, keywords AS 'name',required_result as 'record_number',amt_declared as 'amount',category_ids as service_id,booster_image,`desc`,boosterpacktype FROM booster_packs where id  IN ($ids ) ";

        $data= \ DB::select($query);

        foreach($data as $val){

            $matchThese = ['uploader_id' =>$uid ,'booster_pack_id'=>$val->id];
            $list=CM4TempAppUserData::where($matchThese)->count();
            $val->inserted_record ="$list";

                if( $val->booster_image!='') {
                    $val->images = \Config::get('constants.results.root')."/booster_pic/" . $val->booster_image;
                }else{
                    $val->images = \Config::get('constants.results.root')."/booster_pic/noImage.png" ;
                }
           // $val->images = "https://www.callme4.com/api/public/noImage.png";
            //$val->desc = "Dummy Dummy Dummy";
            /*if($val->images!='') {
                  $val->images = "https://www.callme4.com/api/public/images/" . $val->images;
              }else{
                  $val->images = "https://www.callme4.com/api/public/noImage.png";
              }*/
        }


        if (count($data)!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $data, "device_key" => $token]);
            //  $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $data], "device_key" => $token]);
        } else {
             $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
           // $data = collect(["status" => [ "code" => "105", "message" => \Config::get('constants.results.105')], "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    /**
     * fetch user account Info.
     *
     * @return Response
     */

    public function userAccountInfo() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
           'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        // return $requestData;
        $matchThese = ['id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get();
        $status = $userInfo->count();
       // return $status;
        if($status==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $matchThese = ['uid' => $requestData['uid']];

        $user = CM4PiggyBankAccount::where('bank_name', '!=' , 'blank')->where($matchThese)->get(['id','bank_ifsc_code','bank_name','account_number']);
        $user->count();
        $status = $user->count();
       /* $str =$user[0]["bank_name"];
       $accountExist=strcmp($str,"blank");*/
       //return $accountExist."dddd";

//&& $accountExist!=0
        if ($status!=0 ) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $user[0], "device_key" => $token]);
            //  $data = collect(["status" => ["code" => "100", "message" => \Config::get('constants.results.100'), "data" => $data], "device_key" => $token]);
        } else {
             $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            //$data = collect(["status" => [ "code" => "105", "message" => \Config::get('constants.results.105')], "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

    /**
     * add people.
     *
     * @return Response
     */
    public function addPeopleBooster()
    {

        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        // $requestUser = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        $totalRecord =count($requestData["data"]);
        $insertedRecord =0;
        if(count($requestData["data"])==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.401'),'errorCode'=>'401','errorDesc'=>\Config::get('constants.results.401'), "device_key" => $token]);
            return $result;
        }
        $flag=0;
        // $requestData["data"][0]['location'];
        //  $a= array_key_exists('city',$requestData["data"][0]['location']);

        foreach ($requestData["data"] as $value) {
            // return $value;

            if (!(array_key_exists('bid', $value)
                && array_key_exists('contact_person', $value)
                && array_key_exists('profile_pic', $value)
                && array_key_exists('uploader_id', $value)
                && array_key_exists('profession', $value)
                && array_key_exists('org_name', $value)
                && array_key_exists('age', $value)
                && array_key_exists('gender', $value)
                && array_key_exists('about_me', $value)
                && array_key_exists('contact_no', $value)
                && array_key_exists('start_time', $value)
                && array_key_exists('end_time', $value)
                && array_key_exists('email', $value)
                && array_key_exists('address_source', $value)
                && array_key_exists('location', $value)
                && array_key_exists('locality', $value['location'])
                && array_key_exists('address', $value['location'])
                && array_key_exists('address2', $value['location'])
                && array_key_exists('pincode', $value['location'])
                && array_key_exists('city', $value['location'])
                && array_key_exists('state', $value['location'])
                && array_key_exists('country', $value['location'])
                && array_key_exists('latitude', $value['location'])
                && array_key_exists('longitude', $value['location'])
            )
            ) {
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
                // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
                return $result;
            }

           /*  if (count($value) != 15) {
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
                // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
                return $result;
            } */


            $fields = [
                'contact_person' => $value['contact_person'],
                'uploader_id' => $value['uploader_id'],
                'profession' => $value['profession'],
                'contact_no' => $value['contact_no'],
                'locality' => $value['location']['locality']
            ];
            $rules = [
                'contact_person' => 'required',
                'uploader_id' => 'required',
                'profession' => 'required',
                'contact_no' => 'required',
                'locality' => 'required'
            ];
            $valid = \Validator::make($fields, $rules);
            if ($valid->fails()) {
                return [
                    'status' => '0',
                    'message' => 'validation_failed',
                    'errorCode' => '',
                    'errorDesc' => $valid->errors()
                ];
            }
            //  return $value;

            $matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4UserProfile::where($matchThese)->get();
            $Profilestatus = $user->count();


            $matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4TempAppUserData::where($matchThese)->get();
            $userDatastatus = $user->count();

            if ($Profilestatus == 0 && $userDatastatus == 0) {
                if($value['profile_pic']!="") {
                    $imagedata = $this->imageUpload($value['profile_pic']);
                }else{
                    $imagedata["name"]="";
                }
                $list=$this->multi_implode($value['profession'],",");
                $address=$value['location']['address']."|".$value['location']['address2'];
                $list = implode(',',array_unique(explode(',', $list)));
                
                // return $data;


                $matchThese = ['id' => $value["bid"]];

                $PackInfo = CM4BoosterPack::where($matchThese)->get(['latitude','longitude','range']);
                $status = $PackInfo->count();
                if($status==0){
                    $result = collect(["status" => "0", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
                    return $result;
                }
                if($value["address_source"]==1)//gps
                    $rec_address=$value["location"]["address"];
                if($value["address_source"]==2)//manual
                    $rec_address=$value["location"]["address"]." ".$value["location"]["address2"].
                        " ".$value["location"]["city"].
                        " ".$value["location"]["state"].
                        " ".$value["location"]["country"].
                        " ".$value["location"]["pincode"];



               $latLong= $this->_get_lat_long($rec_address);
                //return $latLong;
                $rec_latitude=$latLong['lat'];
                $rec_longitude=$latLong['long'];
                
				$matchThese = ['id' => $value['uploader_id']];
			$uploader = CM4UserProfile::where($matchThese)->first();
			$uploader_name=$uploader['contact_person'];	  
			$uploader_contact=$uploader['contact_no'];
				
				$data = [
                    "is_booster" => 1,
                    "booster_pack_id" => $value["bid"],
                    "contact_person" => $value["contact_person"],
                    "profile_pic" => $imagedata["name"],
                    "uploader_id" => $value["uploader_id"],
                    "profession" =>  $this->getsearchtags_of_ids($list),
                    "profession_ids" => serialize($value["profession"]),
                    "work_place" => $value["org_name"],
                    "age" => $value["age"],
                    "gender" => $value["gender"],
                    "about_me" => $value["about_me"],
                    "contact_no" => $value["contact_no"],
                    "start_time" => $value["start_time"],
                    "end_time" => $value["end_time"],
                    "email" => $value["email"],
                    "address_source" => $value["address_source"],
                    "locality" =>$value['location']['locality'],
                    "address" => $address,
                    "pincode" => $value['location']['pincode'],
                    "city" => $value['location']['city'],
                    "state" => $value['location']["state"],
                    "country" => $value['location']["country"],
                    "latitude" =>$rec_latitude,
                    "longitude" =>$rec_longitude,
					"uploader_name"=>$uploader_name,
					"uploader_contact"=>$uploader_contact
                ];
				
				
				$booster_latitude= $PackInfo[0]->latitude;
                $booster_longitude= $PackInfo[0]->longitude;
                $booster_range= $PackInfo[0]->range;

                $distance =$this->distance($booster_latitude,$booster_longitude,$rec_latitude,$rec_longitude,'K');

               // return $distance;
                if($distance>$booster_range)
                    $data['status']=2;
                    //array_push($data,['status'=>4]);
//return $data;
                CM4TempAppUserData::create($data);
                $flag=1;
                if($distance<$booster_range)
                $insertedRecord++;



            }
        }
        if($flag==1){
            $data =["total_record"=>$totalRecord,"inserted_record"=>$insertedRecord];
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'), 'errorCode' => '100', 'errorDesc' => \Config::get('constants.results.100'),"data"=>$data, "device_key" => $token]);

        }else{
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.103'), 'errorCode' => '103', 'errorDesc' => \Config::get('constants.results.103'), "device_key" => $token]);

        }

        return response()->json($result, 200);
    }

   public function distance($lat1,$lon1,$lat2,$lon2,$unit) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    function _get_lat_long($address)
    {

        $address = str_replace(" ", "+", $address);
        $region = "Delhi/NCR";
       $json =file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&sensor=false&key=AIzaSyB8CigJhhX9B6jvdtJvWEX7QJILbF6xPks&region=$region");
		
		//$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
        $json = json_decode($json);
		//print_r($json);die;
		$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        $latlong=array('lat'=>$lat,'long'=>$long);
        return $latlong;
    }

    /**
     * select booster pack by user.
     *
     * @return Response
     */
    public function userBoosterPeopleDetails() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('bid', $requestData)

        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'uid' => $requestData['uid'],
            'bid' => $requestData['bid'],
        ];
        $rules = [
            'uid' => 'required',
            'bid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }

        $uid =$requestData['uid'];
        $bid =$requestData['bid'];



        $matchThese = ['uploader_id' =>$uid ,'booster_pack_id'=>$bid];
        $list=CM4TempAppUserData::where($matchThese)
                ->get(['contact_person','profile_pic','profession','contact_no','work_place','status']);


        $list->each(function ($item) {

           /* if($item->profile_pic!='') {
                $item->profile_pic = "https://www.callme4.com/api/public/images/" . $item->profile_pic;
            }else{
                $item->profile_pic = "https://www.callme4.com/api/public/noImage.png";
            }*/

            if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }




        })->values();
        $status=$list->count();
        if ($status!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $list, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);


    }

      /**
     * Show the cm4_offers.
     *
     * @return Response
     */
	public function cm4_offers() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();
	if(Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		if (!(array_key_exists('latitude', $requestData)
             &&array_key_exists('longitude', $requestData)
             &&array_key_exists('uid', $requestData)
        )) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       // return $requestData;
        $uid=$requestData['uid'];

        /* $rec = \DB::table('cm4_user_booster_mapping')
            ->select(\DB::raw("GROUP_CONCAT(bid) as 'ids'"))
            ->where('uid',$uid)
            ->get();

       $ids= $rec[0]->ids==null?0:$rec[0]->ids; */

        $latitude=$requestData['latitude'];
        $longitude=$requestData['longitude'];
//latitude,longitude,
        $query="SELECT id, keywords AS 'name',required_result as 'record_number',amt_declared as 'amount',category_ids as service_id,'booster' as type,1 as type_id,booster_image,`desc`,boosterpacktype,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(latitude))* COS( RADIANS( longitude ) - RADIANS($longitude)) + SIN ( RADIANS($latitude) ) * SIN(RADIANS( latitude )))) AS distance FROM booster_packs HAVING distance < 500 order by distance";

        $data= \ DB::select($query);
		foreach($data as $val)
		{
            if($val->booster_image!="") {
                $val->booster_image = "https://www.callme4.com:8443/uploaded_file/booster_pic/" . $val->booster_image;
            }else{
                $val->booster_image = "https://www.callme4.com:8443/uploaded_file/booster_pic/noImage.png";
            }
        }
	
	 $selectoffers=\ DB::select("select id,content as name,adimage as booster_image,0 as amount,0 as service_id,'' as `desc`,type,type_id,0 as boosterpacktype,0 as distance,0 as record_number from cm4_feeds where type_id !='3'"); 
	  $videoads=array();
	  $listothers=array();
	  $PhotoShootAds=array();
	  if(isset($selectoffers[0])>0)
		{
		foreach($selectoffers as $value)
		{
		$value->booster_image = \Config::get('constants.results.root')."/adimage/".$value->booster_image;	
		if($value->type_id=='3')
		{
		array_push($data,$value);	
		}
		if($value->type_id=='2')
		{
		array_push($data,$value);	
		}
		if($value->type_id=='4')
		{
		array_push($data,$value);	
		}
		}
		
	 }
	  $finalresult=$data; 
	  
	if (count($finalresult)!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" =>$finalresult, "device_key" => $token]);
        } else {
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            }

        return response()->json($data, 200);
	}
	
	
	/**
     * To update the user info.
     *
     * @return Response
     */
    public function removeAcount() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('aid', $requestData)
             )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 1) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'aid' => $requestData['aid']
        ];
        $rules = [
            'aid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }




        $matchThese = ['id' => $requestData['aid']];

        $user = CM4PiggyBankAccount::where($matchThese)->get();
        $status = $user->count();


        if ( $status  != 0) {

            $current_rec = CM4PiggyBankAccount::find($user[0]['id']);
            $current_rec->bank_name = "blank";
            $current_rec->bank_ifsc_code = "blank";
            $current_rec->account_number = "blank";
            $current_rec->updated_at = $created_date;

            if ($current_rec->save()) {

                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
            } else {

                //  $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.101'),'errorCode'=>'101','errorDesc'=>\Config::get('constants.results.101'), "device_key" => $token]);
            }
        } else {


            $result = collect(["status" => "0", "message" => \Config::get('constants.results.129'),'errorCode'=>'129','errorDesc'=>\Config::get('constants.results.129'), "device_key" => $token]);
        }

        return response()->json($result, 200);


    }

    //get category
    public function get_category($pid){
        $details_url = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=parent_id%3A$pid&start=0&rows=1000&wt=json&indent=true";

        $details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
        return $response_arr;
    }


	//Get Category By parent id
		 //get category
    public function get_categorybypid(){
        
		 $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('pid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
		$pid=$requestData['pid'];
		
				
		$details_url = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=parent_id%3A$pid&start=0&rows=1000&wt=json&indent=true";
	//return $details_url;
      
	   $details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
       $grandparent_id="";
	   $grandparent_name="";
	   
     if(!empty($response_arr[0]) && $response_arr[0]['type']=='Service')
	 {
		$segment_parent=$response_arr[0]['parent_id'];
		$segmentparent_url = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=id%3A$segment_parent&start=0&rows=1000&wt=json&indent=true"; 
		  $responseforseg    = file_get_contents($segmentparent_url);
        $responseforseg = json_decode($responseforseg, true);
		$response_segparent=$responseforseg["response"]["docs"];
		
		$segment_id=$response_segparent[0]['parent_id'];
			
		$grandparent_url = "http://172.16.200.35:8983/solr/category/select?q=*%3A*&fq=id%3A$segment_id&start=0&rows=1000&wt=json&indent=true"; 
		 
		  
		  $responseforgrand   = file_get_contents($grandparent_url);
        $responseforgrand = json_decode($responseforgrand, true);	
		$responseforgrand= $responseforgrand["response"]["docs"];
		
		$grandparent_id=$responseforgrand[0]['id'];	
		$grandparent_name=$responseforgrand[0]['category_name'];
	
	 
	 
	 //print_r($response_segparent);die;
	 
	 }
	  $records=[];
	  foreach($response_arr as $value)
        {
			$value['grand_parent_id']= $grandparent_id;
			$value['grand_parent_name']= $grandparent_name;

			if($value['category_id'] !='112864' && $value['category_id'] != '114714'){
            	array_push($records, $value);
            }
		  
     }
	
	 
	 if ($response_arr) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $records, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);
   }
	
	
	//get solr category with group name
	
	//Get Category By parent id
		 //get category
    public function get_testcategorybypid(){
        
		 $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('pid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
		$pid=$requestData['pid'];
		
				
		$details_url = "http://192.168.1.114:8983/solr/category/select?q=*%3A*&fq=parent_id%3A$pid&start=0&rows=1000&wt=json&indent=true";
	//return $details_url;
      
	   $details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);

        $response_arr= $response["response"]["docs"];
       $grandparent_id="";
	   $grandparent_name="";
	   
     if(!empty($response_arr[0]) && $response_arr[0]['type']=='Service')
	 {
		$segment_parent=$response_arr[0]['parent_id'];
		$segmentparent_url = "http://192.168.1.114:8983/solr/category/select?q=*%3A*&fq=id%3A$segment_parent&start=0&rows=1000&wt=json&indent=true"; 
		  $responseforseg    = file_get_contents($segmentparent_url);
        $responseforseg = json_decode($responseforseg, true);
		$response_segparent=$responseforseg["response"]["docs"];
		
		$segment_id=$response_segparent[0]['parent_id'];
			
		$grandparent_url = "http://192.168.1.114:8983/solr/category/select?q=*%3A*&fq=id%3A$segment_id&start=0&rows=1000&wt=json&indent=true"; 
		 
		  
		  $responseforgrand   = file_get_contents($grandparent_url);
        $responseforgrand = json_decode($responseforgrand, true);	
		$responseforgrand= $responseforgrand["response"]["docs"];
		
		$grandparent_id=$responseforgrand[0]['id'];	
		$grandparent_name=$responseforgrand[0]['category_name'];
	
	 
	 
	 //print_r($response_segparent);die;
	 
	 }
	  $records=[];
	  foreach($response_arr as $value)
        {
			 $value['grand_parent_id']= $grandparent_id;
			$value['grand_parent_name']= $grandparent_name;
           
			array_push($records,$value);
		  
     }
	
	 
	 if ($response_arr) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" => $records, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }

        return response()->json($data, 200);
   }
	
	
	
	
	
    public function today_timing($dayandtime)
    {
        $getdaytime = explode(',',$dayandtime);
       
		if(count($getdaytime)>1)
		{	
		
		foreach ($getdaytime as $value) {
            $day[] = explode('|', $value);
        }
       
		$output_array = array();
        for ($i = 0; $i < count($day); $i++) {
            for ($j = 0; $j < count($day[$i]); $j++) {
                $output_array[] = $day[$i][$j];
            }
        }
        $t = date('d-m-Y');
        $weekday = date('l', strtotime($t));
        //$trimmed_array=array_map('trim',$output_array);
		$key = array_search($weekday,$output_array);
        return $output_array[$key + 1];
		}
		else
		{
			return $dayandtime;
		}
    }

	
	
	
	
    /**
     * To mark the user favourite.
     *
     * @return Response
     */
    public function markFavourite()
    {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }


        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('favid', $requestData)
        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'uid' => $requestData['uid'],
            'favid' => $requestData['favid'],
        ];
        $rules = [
            'uid' => 'required',
            'favid' => 'required',

        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }


        $matchThese = ['uid' => $requestData['uid'], 'favid' => $requestData['favid']];

        $userInfo = CM4UserFavourite::where($matchThese)->get(['id','status']);
        $status = $userInfo->count();
        if ($status != 0) {

            $current_rec = CM4UserFavourite::find($userInfo[0]['id']);
            $current_rec->status = $userInfo[0]['status']==1? 0: 1;
            $current_rec->save();
            $userInfo = CM4UserFavourite::where($matchThese)->get(['status']);
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'), 'errorCode' => '100', 'errorDesc' => \Config::get('constants.results.100'), 'data' => $userInfo[0], "device_key" => $token]);
           // return $result;
        } else {



        $data = [
            "uid" => $requestData['uid'],
            "favid" => $requestData['favid'],
            "updated_at" => $created_date,
            "created_at" => $created_date
        ];
            $userInfo=    CM4UserFavourite::create($data);

            $userInfo = CM4UserFavourite::where($matchThese)->get(['status']);


        $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'), 'errorCode' => '100', 'errorDesc' => \Config::get('constants.results.100'), 'data' => $userInfo[0], "device_key" => $token]);
    }


        return response()->json($result, 200);


    }

    /**
     * fetch user favourate list.
     *
     * @return Response
     */

    public function fetchfavourite() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        // return $requestData;
        $matchThese = ['id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get();
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $matchThese = ['uid' => $requestData['uid'],'status'=>0];

        $user = CM4UserFavourite::where($matchThese)->get();
        $status = $user->count();

        $rec = \DB::table('cm4_user_favourite')
            ->select(\DB::raw("GROUP_CONCAT(favid) as 'ids'"))
            ->where('uid',$requestData['uid'])
            ->get();


        if($rec[0]->ids!="")
        {
            $ids = $rec[0]->ids;
            //return $ids;
            $query="SELECT id,user_id,call_time,contact_no,contact_person,user_name,address,locality,cc_fdail,longitude,profile_pic,latitude,category as services,live_status
            FROM cm4_user_profile where id  IN ($ids ) ";

            $userdata= \ DB::select($query);
           //// return $userdata;
            $records=[];
            foreach($userdata as $val){
                $val->user_name=trim($val->user_name);
                $val->cc_fdail=$val->cc_fdail;
                $val->user_id=$val->user_id;
                $val->contact_no=$val->contact_no;
               if($val->contact_person=="" || $val->contact_person==" ") {
                   $val->contact_person= $val->user_name;
                }
                $val->latitude=$val->latitude;
                $val->longitude=$val->longitude;


                $val->address=$val->address;
             
                $val->locality=$val->locality;
                $matchThese=['favid'=>$val->id,'status'=>1];
                $usertime = CM4UserFavourite::where($matchThese)->get(['created_at']);
                if($usertime->count()>0) {
                    $val->favourite_date = $usertime[0]['created_at']->format('Y-m-d H:i:s');
                   
					
                }else{
                    $val->favourite_date="";
                }
				 if ($val->call_time != "" && $val->live_status=='0') {
                        $time = $this->today_timing($val->call_time);
                         $time=str_replace("-","|",$time);
						$val->today_timing = $time;
                    }
					else
					{
					 $val->today_timing =$val->call_time;	
					}
                /* if($val['profile_pic']!='') {
                     $val['profile_pic'] = "https://www.callme4.com/api/public/images/" . $val['profile_pic'];
                 }else{
                    // $val['profile_pic'] = "https://www.callme4.com/api/public/noImage.png";
                     $val['profile_pic'] = "";
                 }*/

                if($val->profile_pic!='') {
                    $val->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $val->profile_pic;
                }else{
                    $val->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }
				$matchThese=['uid'=> $requestData['uid'],'favid'=>$val->id,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val->favourite_status=  $user->count()>0?1:0;

                array_push($records,$val);

            }
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $userdata
                , "device_key" => $token]);


            //return 0;
        }
        else{
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
          //  return 1;
        }



        return response()->json($data, 200);


    }


    /**
     * fetch user call history.
     *
     * @return Response
     */

    public function callHistory() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
        
		//Version Code
		$userappversion="";
		$latestversion="116";
		if(array_key_exists('version_code',$requestData))
		{
		$userappversion=$requestData['version_code'];
		$userid=$requestData['uid'];
		$matchThese = ['user_id' => $requestData['uid']];

        $appinfo = CM4UserVersion::where($matchThese)->get();
        $statuscount = $appinfo->count();
		if($statuscount==0)
		{
		  $appvesion =[
                'user_id' => $requestData['uid'],
                'user_app_version' => $userappversion,
					 ];	
			CM4UserVersion::create($appvesion);
		}
		else
		{
		CM4UserVersion::where('user_id',$userid)->update(['user_app_version' =>$userappversion]);	
		}
		
		}	
		
		$isupdate=0;
		if(array_key_exists('maxupdateddate',$requestData))
		{
		$queryformaxdate="SELECT MAX(updated_at) AS maxdate FROM cm4_do_you_know";
				$maxdate= \ DB::select($queryformaxdate);
			    $maxdaterecent=isset($maxdate[0]->maxdate)?$maxdate[0]->maxdate:0;
			if($requestData['maxupdateddate']!=$maxdaterecent)
			{
			$isupdate=1;	
			}
		
		}
		// return $requestData;
        $matchThese = ['user_id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get(['id']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1","latest_version"=>$latestversion,"is_update"=>$isupdate, "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $username = $requestData['uid'];
		$userid=$userInfo[0]->id;	


        $data =\DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $username)
            ->get();
      if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
	
        }else {
			
          $user_id = 0;
      }

        if($user_id !=0 && $mobilenumber!='')
        {

            $data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=', $mobilenumber)
                ->orWhere('src', '=', $mobilenumber)
                ->where('calledstation', '!=', 'src')
				->where('sipiax', '=', '0')
                ->orderBy('id', 'desc')
                ->take(20)
                ->get();

            $type="";
			if(count($data)>0)
			{
			foreach($data as $val)
			{ $getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $val->card_id)
            ->get();
			$src='';
      if (count($getsrc) > 0){
           
          $src=$getsrc[0]->phone;
         
	
        }
		$val->type=$this->getcalltype($val->calledstation,$src,$val->sessiontime,$mobilenumber);	
			
			$val->id_did="";	
			$val->id_card_package_offer="";	
			
			$new_date = date('d-M-Y',strtotime($val->starttime));		
	        $time = date('h:i A', strtotime($val->starttime));	
			
			$val->starttime=$time;
			$val->startdate=$new_date;
			$stop_date = date('d-M-Y',strtotime($val->stoptime));		
	        $stoptime = date('h:i A', strtotime($val->stoptime));	
			
			$val->stoptime=$stoptime;
			$val->stopdate=$stop_date;
			if($src==$mobilenumber)
							{
								//outgoing
								$usernamemob=$this->getusernamefrommobile($val->calledstation);
							}
							elseif($val->calledstation==$mobilenumber)
							{
								//incoming
								$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
							}
			
			
			if($usernamemob!="")
			{
			$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where user_id='".$usernamemob."'");
		
		if(isset($selectqry)>0)
		{
			foreach($selectqry as $value)
			{
			
			$matchThese=['uid'=> $userid,'favid'=>$value->id,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $value->favourite_status=  $user->count()>0?1:0;
			if(trim($value->contact_person)=='') {
                    $value->contact_person =$value->user_name;
                }
			 if($value->profile_pic!='') {
                    $value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
                }else{
                    $value->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }
			if (!empty($value))
			$val->userdata=array($value);			
			else
			$val->userdata=array();	
			}
			}
			}
			else
			{
			$val->userdata=array();
			}
		}
	}
		
        }
       
        if($data)
        {
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);


        }
        else{
            $data = collect(["status" => "1","latest_version"=>$latestversion,"is_update"=>$isupdate, "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','data' => array(),"device_key" => $token]);
            //  return 1;
        }



        return response()->json($data, 200);


    }
	
	
	
    /**
     * fetch Earn And Expenses.
     *
     * @return Response
     */

    public function fetch_earn_expenses() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       
		// return $requestData;
        $matchThese = ['user_id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get(['id']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $username = $requestData['uid'];
		$userid=$userInfo[0]->id;	


        $data =\DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $username)
            ->get();
      if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
		  $piggy_bal=round($data[0]->credit,2);
	
        }else {
			
          $user_id = 0;
		  $piggy_bal=0.0;
      }
  if($user_id !=0 && $mobilenumber!='')
        {
			$qry="SELECT * FROM `cc_call` WHERE (calledstation='".$mobilenumber."' OR src='".$mobilenumber."') and calledstation !=src and sipiax=0 and sessionbill>0 order by id desc";
		$data= \DB::connection('a2billing')->select($qry);
            
			
			/* $data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=', $mobilenumber)
                ->orWhere('src', '=', $mobilenumber)
                ->where('calledstation', '!=', 'src')
				->where('sipiax', '=', '0')
				->where('sessionbill', '>', '0')
                ->orderBy('id', 'desc')
                ->get(); */

            $type="";
			
			if(count($data)>0)
			{
			foreach($data as $val)
			{ $getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $val->card_id)
            ->get();
			$src='';
      if (count($getsrc) > 0){
           
          $src=$getsrc[0]->phone;
         
	
        }
		$val->type=$this->getcalltype($val->calledstation,$src,$val->sessiontime,$mobilenumber);	
		if($val->type=='Incoming Call')
		{
		$val->sessionbill='+ '.$val->sessionbill;	
		}
		if($val->type=='Outgoing Call')
		{
		$val->sessionbill='- '.$val->sessionbill;	
		}
			$val->id_did="";	
			$val->id_card_package_offer="";	
			
			$new_date = date('d-M-Y',strtotime($val->starttime));		
	        $time = date('h:i A', strtotime($val->starttime));	
			
			$val->starttime=$time;
			$val->startdate=$new_date;
			$stop_date = date('d-M-Y',strtotime($val->stoptime));		
	        $stoptime = date('h:i A', strtotime($val->stoptime));	
			
			$val->stoptime=$stoptime;
			$val->stopdate=$stop_date;
			if($src==$mobilenumber)
							{
								//outgoing
								$usernamemob=$this->getusernamefrommobile($val->calledstation);
							}
							elseif($val->calledstation==$mobilenumber)
							{
								//incoming
								$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
							}
			
			
			if($usernamemob!="")
			{
			$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where user_id='".$usernamemob."'");
		
		if(isset($selectqry)>0)
		{
			foreach($selectqry as $value)
			{
			
			$matchThese=['uid'=> $userid,'favid'=>$value->id,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $value->favourite_status=  $user->count()>0?1:0;
			if(trim($value->contact_person)=='') {
                    $value->contact_person =$value->user_name;
                }
			 if($value->profile_pic!='') {
                    $value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
                }else{
                    $value->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                }
			if (!empty($value))
			$val->userdata=array($value);			
			else
			$val->userdata=array();	
			}
			}
			}
			else
			{
			$val->userdata=array();
			}
		}
	}
		
        }
       
        if($data)
        {
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);


        }
        else{
            $data = collect(["status" => "1",'piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150',"message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','data' => array(),"device_key" => $token]);
            //  return 1;
        }
  return response()->json($data, 200);

    }
	
 	
	
	  /**
     * fetch Earn And Expenses.
     *
     * @return Response
     */

    public function fetch_earn_expenses_new() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       
		// return $requestData;
        $matchThese = ['user_id' => $requestData['uid']];
		$other_no="";	
        $userInfo = CM4UserProfile::where($matchThese)->get(['id','marital_status']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $username = $requestData['uid'];
		$userid=$userInfo[0]->id;
		if($userInfo[0]->marital_status){
			$other_no= $userInfo[0]->marital_status;
		}	

//print_r($userInfo[0]->marital_status);exit();
        $data =\DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $username)
            ->get();
      if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
		  $piggy_bal=round($data[0]->credit,2);
	
        }else {
			
          $user_id = 0;
		  $piggy_bal=0.0;
      }
  
  if($user_id !=0 && $mobilenumber!='')
        {
        	if(trim($other_no)){
        		$qry="SELECT * FROM `cc_call` WHERE ((calledstation='".$mobilenumber."' OR src='".$mobilenumber."') or (calledstation='".$other_no."' OR src='".$other_no."')) and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc limit 30";
        	}else{
        		$qry="SELECT * FROM `cc_call` WHERE (calledstation='".$mobilenumber."' OR src='".$mobilenumber."') and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc ";
        	}
			
		$data= \DB::connection('a2billing')->select($qry);
            
			
			/* $data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=', $mobilenumber)
                ->orWhere('src', '=', $mobilenumber)
                ->where('calledstation', '!=', 'src')
				->where('sipiax', '=', '0')
				->where('sessionbill', '>', '0')
                ->orderBy('id', 'desc')
                ->get(); */

            $type="";
			
			if(count($data)>0)
			{
			foreach($data as $val)
			{ $getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $val->card_id)
            ->get();
			$src='';
      if (count($getsrc) > 0){
           
          $src=$getsrc[0]->phone;
         
	
        }
		
		$val->type=$this->getcalltype($val->calledstation,$src,$val->sessiontime,$mobilenumber);	
		if($val->type=='Incoming Call')
		{
		//$val->sessionbill='+ '.$val->sessionbill;	
		 $val->sessionbill='+ '.preg_replace('/-+/','', $val->sessionbill); 
		}
		if($val->type=='Outgoing Call')
		{
		//$val->sessionbill='- '.$val->sessionbill;	
		$val->sessionbill='- '.preg_replace('/-+/','', $val->sessionbill); 
		}
			
			$val->id_did="";	
			$val->id_card_package_offer="";	
			
			$new_date = date('d-M-Y',strtotime($val->starttime));		
	        $time = date('h:i A', strtotime($val->starttime));	
			
			$val->starttime=$time;
			$val->startdate=$new_date;
			$stop_date = date('d-M-Y',strtotime($val->stoptime));		
	        $stoptime = date('h:i A', strtotime($val->stoptime));	
			
			$val->stoptime=$stoptime;
			$val->stopdate=$stop_date; //print_r($src);
			if($src==$mobilenumber)
							{
								//outgoing
								$usernamemob=$this->getusernamefrommobile($val->calledstation);
								$usernamemob1=$val->calledstation;
							}
							elseif($val->calledstation==$mobilenumber)
							{
								//incoming
								$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
								$usernamemob1=$val->src;
							}
			
			//print_r($usernamemob);
						/*if($usernamemob!=""){*/
							if($usernamemob!=""){
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where user_id='".$usernamemob."'");
							}else{
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where contact_no='".$usernamemob1."' or marital_status='".$usernamemob1."' ");
							}
						
						//print_r($selectqry);exit();
							if(!empty($selectqry)){
								foreach($selectqry as $value){
									$matchThese=['uid'=> $userid,'favid'=>$value->id,'status'=>1];
									$user = CM4UserFavourite::where($matchThese)->get();
									$value->favourite_status=  $user->count()>0?1:0;
									if(trim($value->contact_person)=='') {
										$value->contact_person =$value->user_name;
									}
									if($value->profile_pic!='') {
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
									}else{
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
									}
									if (!empty($value))
										$val->userdata=array($value);			
									else
									$val->userdata=array();	
								}
							}else{
								$val->userdata=array();
							}
		}
	}
		
        }
       
        if($data)
        {
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);


        }
        else{
            $data = collect(["status" => "1",'piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150',"message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','data' => array(),"device_key" => $token]);
            //  return 1;
        }
  return response()->json($data, 200);

    }
	
	
	
	  /**
     * fetch Earn And Expenses.
     * updated on 26/06/2018 07:25
     * @return Response
     */

    public function fetch_earn_expenses_ctry_code() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       
		// return $requestData;
        $matchThese = ['user_id' => $requestData['uid']];
		$other_no="";	
        $userInfo = CM4UserProfile::where($matchThese)->get(['id','marital_status']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $username = $requestData['uid'];
		$userid=$userInfo[0]->id;
		if($userInfo[0]->marital_status){
			$other_no= $userInfo[0]->marital_status;
		}	

//print_r($userInfo[0]->marital_status);exit();
        $data =\DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $username)
            ->get();
      if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
		  $piggy_bal=round($data[0]->credit,2);
	
        }else {
			
          $user_id = 0;
		  $piggy_bal=0.0;
      }
  
  if($user_id !=0 && $mobilenumber!='')
        {
        	if(trim($other_no)){
        		$qry="SELECT * FROM `cc_call` WHERE ((calledstation like '%".$mobilenumber."' OR src='".$mobilenumber."') or (calledstation='".$other_no."' OR src='".$other_no."')) and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc limit 30";
        	}else{
        		 $qry="SELECT * FROM `cc_call` WHERE (calledstation like '%".$mobilenumber."' OR src like '%".$mobilenumber."') and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc limit 50";
				}
		
		$data= \DB::connection('a2billing')->select($qry);
            
			
			/* $data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=', $mobilenumber)
                ->orWhere('src', '=', $mobilenumber)
                ->where('calledstation', '!=', 'src')
				->where('sipiax', '=', '0')
				->where('sessionbill', '>', '0')
                ->orderBy('id', 'desc')
                ->get(); */

            $type="";
			
			if(count($data)>0)
			{
			foreach($data as $val)
			{ $getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $val->card_id)
            ->get();
			$src='';
      if (count($getsrc) > 0){
           
          $src=$getsrc[0]->phone;
         
	
        }
		
		$val->type=$this->getcalltype(substr($val->calledstation,-10),$src,$val->sessiontime,$mobilenumber);	
		if($val->type=='Incoming Call')
		{
		//$val->sessionbill='+ '.$val->sessionbill;	
		 $val->sessionbill='+ '.preg_replace('/-+/','', $val->sessionbill); 
		}
		if($val->type=='Outgoing Call')
		{
		//$val->sessionbill='- '.$val->sessionbill;	
		$val->sessionbill='- '.preg_replace('/-+/','', $val->sessionbill); 
		}
			
			$val->id_did="";	
			$val->id_card_package_offer="";	
			
			$new_date = date('d-M-Y',strtotime($val->starttime));		
	        $time = date('h:i A', strtotime($val->starttime));	
			
			$val->starttime=$time;
			$val->startdate=$new_date;
			$stop_date = date('d-M-Y',strtotime($val->stoptime));		
	        $stoptime = date('h:i A', strtotime($val->stoptime));	
			
			$val->stoptime=$stoptime;
			$val->stopdate=$stop_date; //print_r($src);
			if($src==$mobilenumber)
							{
								//outgoing
								$usernamemob=$this->getusernamefrommobile($val->calledstation);
								$usernamemob1=$val->calledstation;
							}
							elseif($val->calledstation==$mobilenumber)
							{
								//incoming
								$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
								$usernamemob1=$val->src;
							}
			
							if($usernamemob!=""){
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where user_id='".$usernamemob."'");
							}else{
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where contact_no='".$usernamemob1."' or marital_status='".$usernamemob1."' ");
							}
						
						//print_r($selectqry);exit();
							if(!empty($selectqry)){
								foreach($selectqry as $value){
									$matchThese=['uid'=> $userid,'favid'=>$value->id,'status'=>1];
									$user = CM4UserFavourite::where($matchThese)->get();
									$value->favourite_status=  $user->count()>0?1:0;
									if(trim($value->contact_person)=='') {
										$value->contact_person =$value->user_name;
									}
									if($value->profile_pic!='') {
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
									}else{
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
									}
									if (!empty($value))
										$val->userdata=array($value);			
									else
									$val->userdata=array();	
								}
							}else{
								$val->userdata=array();
							}
		}
	}
		
        }
       
        if($data)
        {
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);


        }
        else{
            $data = collect(["status" => "1",'piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150',"message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','data' => array(),"device_key" => $token]);
            //  return 1;
        }
  return response()->json($data, 200);

    }
	
	
	   public function fetch_earn_expenses_latest() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)
        ) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
       /*  if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
        $fields = [
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       
		// return $requestData;
        $matchThese = ['user_id' => $requestData['uid']];
		$other_no="";	
        $userInfo = CM4UserProfile::where($matchThese)->get(['id','marital_status']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }
        $username = $requestData['uid'];
		$userid=$userInfo[0]->id;
		if($userInfo[0]->marital_status){
			$other_no= $userInfo[0]->marital_status;
		}	

//print_r($userInfo[0]->marital_status);exit();
        $data =\DB::connection('a2billing')->table('cc_card')
            ->where('username', '=', $username)
            ->get();
      if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
		  $piggy_bal=round($data[0]->credit,2);
	
        }else {
			
          $user_id = 0;
		  $piggy_bal=0.0;
      }
  
  if($user_id !=0 && $mobilenumber!='')
        {
        	if(trim($other_no)){
        		$qry="SELECT * FROM `cc_call` WHERE ((calledstation like '%".$mobilenumber."' OR src='".$mobilenumber."') or (calledstation='".$other_no."' OR src='".$other_no."')) and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc limit 30";
        	}else{
        		 $qry="SELECT * FROM `cc_call` WHERE (calledstation like '%".$mobilenumber."' OR src like '%".$mobilenumber."') and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc limit 50";
				}
	
		$data= \DB::connection('a2billing')->select($qry);
            
			
			/* $data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=', $mobilenumber)
                ->orWhere('src', '=', $mobilenumber)
                ->where('calledstation', '!=', 'src')
				->where('sipiax', '=', '0')
				->where('sessionbill', '>', '0')
                ->orderBy('id', 'desc')
                ->get(); */

            $type="";
			
			if(count($data)>0)
			{
			foreach($data as $val)
			{ $getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $val->card_id)
            ->get();
			$src='';
      if (count($getsrc) > 0){
           
          $src=$getsrc[0]->phone;
         
	
        }
		
		$val->type=$this->getcalltype(substr($val->calledstation,-10),$src,$val->sessiontime,$mobilenumber);	
		if($val->type=='Incoming Call')
		{
		//$val->sessionbill='+ '.$val->sessionbill;	
		 $val->sessionbill='+ '.preg_replace('/-+/','', $val->sessionbill); 
		}
		if($val->type=='Outgoing Call')
		{
		//$val->sessionbill='- '.$val->sessionbill;	
		$val->sessionbill='- '.preg_replace('/-+/','', $val->sessionbill); 
		}
			
			$val->id_did="";	
			$val->id_card_package_offer="";	
			
			$new_date = date('d-M-Y',strtotime($val->starttime));		
	        $time = date('h:i A', strtotime($val->starttime));	
			
			$val->starttime=$time;
			$val->startdate=$new_date;
			$stop_date = date('d-M-Y',strtotime($val->stoptime));		
	        $stoptime = date('h:i A', strtotime($val->stoptime));	
			
			$val->stoptime=$stoptime;
			$val->stopdate=$stop_date; //print_r($src);
			if($src==$mobilenumber)
							{
								//outgoing
								$usernamemob=$this->getusernamefrommobile(substr($val->calledstation,-10));
								$usernamemob1=substr($val->calledstation,-10);
							}
							elseif(substr($val->calledstation,-10)==$mobilenumber)
							{
								//incoming
								$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
								$usernamemob1=$val->src;
							}
			
							if($usernamemob!=""){
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where user_id='".$usernamemob."'");
							}else{
								$selectqry=\ DB::select("select id,contact_person,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service from cm4_user_profile where contact_no='".$usernamemob1."' or marital_status='".$usernamemob1."' ");
							}
						
						//print_r($selectqry);exit();
							if(!empty($selectqry)){
								foreach($selectqry as $value){
									$matchThese=['uid'=> $userid,'favid'=>$value->id,'status'=>1];
									$user = CM4UserFavourite::where($matchThese)->get();
									$value->favourite_status=  $user->count()>0?1:0;
									if(trim($value->contact_person)=='') {
										$value->contact_person =$value->user_name;
									}
									if($value->profile_pic!='') {
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
									}else{
										$value->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
									}
									if (!empty($value))
										$val->userdata=array($value);			
									else
									$val->userdata=array();	
								}
							}else{
								$val->userdata=array();
							}
		}
	}
		
        }
       
        if($data)
        {
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);


        }
        else{
            $data = collect(["status" => "1",'piggy_bal'=>$piggy_bal,'rate_opt'=>'0,1,2,3,4,5,10,20,30,40,50,150',"message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','data' => array(),"device_key" => $token]);
            //  return 1;
        }
  return response()->json($data, 200);

    }
	

	
	
	/* //GET TODAY CALL ACCOUTING OF USER
		 public function getUserCalltime() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        $todaydate=date('Y-m-d');
		
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        
		$sndr_dailNo=$requestData['contact_no'];
		$sndr_Uid=$requestData['uid'];
		$callcount=0;
			$data = [
                "uid" => $sndr_Uid,
                "contact_no" =>$sndr_dailNo,
				];
            
			$querycalltime="SELECT sum(real_sessiontime) as totalsumcall  from cc_call where src='".$sndr_dailNo."' and date(starttime)='".$todaydate."'";
  $callcountquery= \ DB::connection('a2billing')->select($querycalltime);
		 $sumtime=$callcountquery[0]->totalsumcall;
		 if($sumtime<1800)
		 { 
		$remailtime=1800-$sumtime;
		 }
		 else
		 {
			$remailtime=0; 
		 }
		 
		 $result = collect(["status" => "1", "message" =>"","remaining_time"=>$remailtime,'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
		return response()->json($result, 200);
	
	}  */
	
	 //GET USER CALL TIME NEW
	  public function getUserCalltime() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        $todaydate=date('Y-m-d');
		
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        
		$sndr_dailNo=$requestData['contact_no'];
		$sndr_Uid=$requestData['uid'];
		$callcount=0;
			$data = [
                "uid" => $sndr_Uid,
                "contact_no" =>$sndr_dailNo,
				];
            
		$querycalltime="SELECT sum(real_sessiontime) as totalsumcall  from cc_call where src='".$sndr_dailNo."' and date(starttime)='".$todaydate."'";
  $callcountquery= \ DB::connection('a2billing')->select($querycalltime);
		 $sumtime=$callcountquery[0]->totalsumcall;
		 
		  $getearned = ['uid'=>$sndr_Uid];
	$list = CM4UserminEarned::where($getearned)->get();
	$usermincount = $list->count();
		
			$remailtime=0; 
			$remainingmin=0;
			$remaininginsec=0;
			$earned_min=0;
			$earnedinsec=0;
			$remainingsec=0;
			if($usermincount>0)
			{
			$remainingmin=$list[0]['remaining_min'];
			$updatedtime=$list[0]['updated_at'];
			$remaininginsec=$remainingmin*60;
			$earned_min=$list[0]['earned_min'];
			$earnedinsec=$earned_min*60;
			}
			
		//print_r($list[0]['remaining_min']);die;
		 
		 if($usermincount>0 && ($remainingmin)>0)
		 {
			
			if($sumtime>=1800)
			{
	$querycalltime="SELECT real_sessiontime as real_sessiontime,starttime from cc_call where src='".$sndr_dailNo."' and date(starttime)='".$todaydate."' order by id desc limit 1";
  $callgetquery= \ DB::connection('a2billing')->select($querycalltime);
		 $lastsession=$callgetquery[0]->real_sessiontime;
		 $lastdate=$callgetquery[0]->starttime;	
		
			if($updatedtime!=$lastdate)
			{

			$toupdate=$lastsession/60;
			$remainingmin=$remainingmin-$toupdate;
			\ DB::statement("UPDATE cm4_earned_min SET remaining_min ='".$remainingmin."',updated_at='".$lastdate."' where uid='".$sndr_Uid."'");	
			
			$list = CM4UserminEarned::where($getearned)->get();
			$getlatest = $list->count();
			$remainingmin=$list[0]['remaining_min'];
			$remainingsec=$remainingmin*60;
					
			}
			else
			{
			
			$remainingmin=$list[0]['remaining_min'];
			$remainingsec=$remainingmin*60;	
			}
			}
			else
			{
			  
			  $remainingsec=($remaininginsec+1800)-$sumtime;
			}
			
		}	 
		 else
		 {
		 if($sumtime<1800)
		 { 
		$remainingsec=1800-$sumtime;
		 }
		 else
		 {
		$remainingsec=0;	 
		 }
		 }
		 $result = collect(["status" => "1", "message" =>"","remaining_time"=>$remainingsec,'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
		return response()->json($result, 200);
		}	
	
	
	
	
	
	
	//GET USERNAME BY MOBILE
	public function getusernamefrommobile($mobilenumber){
		$mobilenumber = substr($mobilenumber, -10);
	$getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=', $mobilenumber)
            ->get(['username']);
				 if(count($getsrc)>0){
				 
				  $usernamemob=$getsrc[0]->username;
					 return $usernamemob;
				}
				
     }
	
	//Get Username
	public function getusername($userid) 
	{
	if($userid!='' && $userid!=0){
       
		$getsrc =\DB::connection('a2billing')->table('cc_card')
            ->where('id', '=', $userid)
            ->get(['username']);
				 if(count($getsrc)>0){
				 
				  $usernamemob=$getsrc[0]->username;
				 }

		return $usernamemob; 
		}
    }

	// GET CALL TYPE
	public function getcalltype($calledstation,$src,$callduration,$currentmonumber){
	$type='';
	if($currentmonumber!=''){
	
	   if($callduration==0 && $calledstation==$currentmonumber){
					$type="Missed Call";
		}elseif($callduration==0 && $src==$currentmonumber){
					$type="Outgoing Call";
		}elseif($callduration>0 && $src==$currentmonumber){
					$type="Outgoing Call";
		}elseif($callduration>0 && $calledstation==$currentmonumber){
					$type="Incoming Call";
		}
	}
	return $type;
}



    public function userinfoMobile() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
//return "hi";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('phone', $requestData)

        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 1) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $requestData['phone']
        ];
        $rules = [
            'phone' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            /*return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];*/
        }

        $phone=$requestData['phone'];



            $details_url = "http://172.16.200.35:8983/solr/search/select?q=*%3A*&fq=contact_no%3A$phone&wt=json&indent=true";


        $details_url = preg_replace('!\s+!', '+', $details_url);
        // return $details_url;
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);
        // $coll_res =(object)$response;
        $response["responseHeader"]["params"]["fq"]="CallMe4";

        $response_arr= $response["response"]["docs"];
        $records=[];
        $tags =[];
        foreach($response_arr as $val){
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
            else
            {
                $val['contact_person']="";
            }
            
			$val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="") {
                $time= $this->today_timing($val['call_time']);
                 $time=str_replace("-","|",$time);
				$val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(  array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }else{
                $val['tags']="";
            }


            if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            array_push($records,$val);

        }

        $list = implode(',',array_unique($tags));
        $newlist=explode(',',$list);
        $newtags=implode(',',array_unique($newlist));
        $response["response"]["tags"]=$newtags;
        $response["response"]["docs"]=$records;



        if ($response["response"]["numFound"]!=0) {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response["response"]["docs"], "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }

	//update user in solr
  function _update_by_username_solr($user_id)
	{
		// check and find the records from the userprofile table
		$tags="";
		$sql1="select id,category_ids,cc_fdail,user_id,user_name,contact_person,contact_no,profile_pic,category,latitude,longitude,address,locality,call_time,user_searchid from cm4_user_profile where id='$user_id'";
			
			 $userdata= \ DB::select($sql1);
			
			if(count($userdata)>0)
			{
				
			 $details_url = "http://172.16.200.35:8983/solr/search/update?stream.body=%3Cdelete%3E%3Cquery%3Eid:$user_id%3C/query%3E%3C/delete%3E&commit=true";
        $details_url = preg_replace('!\s+!', '+', $details_url);
		$response    = file_get_contents($details_url);
				
				$Doc_Id=$userdata[0]->id;
				$username=$userdata[0]->user_id;
				$cc_fdail=$userdata[0]->cc_fdail;
				$user_name=$userdata[0]->user_name;
				$contact_no=$userdata[0]->contact_no;
				$category=$userdata[0]->category;
				$call_time=$userdata[0]->call_time;
				$contact_person=$userdata[0]->contact_person;
				$profile_pic=$userdata[0]->profile_pic;
				$latitude=$userdata[0]->latitude;
				$longitude=$userdata[0]->longitude;
				$address=$userdata[0]->address;
				$locality=$userdata[0]->locality;
				$user_searchid=$userdata[0]->user_searchid;

				$category_ids=$userdata[0]->category_ids;
					if($category_ids=="")
					{
					$category_ids=0;	
					}
					
					$geolocation=$latitude.",".$longitude;
					$qry="SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($category_ids) and `cm4_categories`.`type_id`=1";
					
					 $gettags= \DB::select($qry);
	
			if(count($gettags)>0)
			{
			$tags=$gettags[0]->tags;
			}	
				 	if($tags=="")
					{
					$tags="Others";	
					}
					if($contact_person=="")
					{
					$contact_person="";	
					}
					
					$update=array(
						'id' => $Doc_Id,
						"user_id" => array(
							'set' => $username
						),
						"call_time" => array(
							'set' => $call_time
						),
						
						"cc_fdail" => array(
							'set' => $cc_fdail
						),
						"live_status" => array(
							'set' => 1
						),
						"contact_person" => array(
							'set' => $contact_person
						),
						"user_name" => array(
							'set' => $user_name
						)
						,
						"contact_no" => array(
							'set' => $contact_no
						)
						,
						"latitude" => array(
							'set' => $latitude
						)
						,
						"longitude" => array(
							'set' => $longitude
						)
						,
						"geolocation" => array(
							'set' => $geolocation
						)
						,
						"category" => array(
							'set' => $category
						)
						,
						"address" => array(
							'set' => isset($address)?$address:""
						)
						,
						"locality" => array(
							'set' => isset($locality)?$locality:""
						)
						,
						"category_ids" => array(
							'set' => isset($category_ids)?$category_ids:"0"
						)
						,
						
						"service" => array(
							'set' => isset($category)?$category:""
						)
						,
						"profile_pic" => array(
							'set' => isset($profile_pic)?$profile_pic:""
						)
						,
						"tags" => array(
							'set' => isset($tags)?$tags:""
						),
						
						"user_searchid" => array(
							'set' => isset($user_searchid)?$user_searchid:""
						),
						
					);
					
				$update = json_encode(array($update));
					
				$ch = curl_init('http://172.16.200.35:8983/solr/search/update?commit=true');
				
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);

				// Return transfert
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Set type of data sent
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				//echo "<pre>"; print_r($output);
				// Get response code
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($responseCode == 200)
				{
					return true;
				
				}
				else
				{
					return false;
					
				}
			}
		}
		
			//update user in solr
  function _update_premium_solr($user_id)
	{
		// check and find the records from the userprofile table
		$tags="";
		$sql1="select id,category_ids,cc_fdail,user_id,user_name,contact_person,contact_no,profile_pic,category,latitude,longitude,address,locality,call_time,user_searchid from cm4_premium_customer where id='$user_id'";
			
			 $userdata= \ DB::select($sql1);
			
			if(count($userdata)>0)
			{
				
			 $details_url = "http://172.16.200.35:8983/solr/premium_search/update?stream.body=%3Cdelete%3E%3Cquery%3Eid:$user_id%3C/query%3E%3C/delete%3E&commit=true";
        $details_url = preg_replace('!\s+!', '+', $details_url);
		$response    = file_get_contents($details_url);
				
				$Doc_Id=$userdata[0]->id;
				$username=$userdata[0]->user_id;
				$cc_fdail=$userdata[0]->cc_fdail;
				$user_name=$userdata[0]->user_name;
				$contact_no=$userdata[0]->contact_no;
				$category=$userdata[0]->category;
				$call_time=$userdata[0]->call_time;
				$contact_person=$userdata[0]->contact_person;
				$profile_pic=$userdata[0]->profile_pic;
				$latitude=$userdata[0]->latitude;
				$longitude=$userdata[0]->longitude;
				$address=$userdata[0]->address;
				$locality=$userdata[0]->locality;
				$user_searchid=$userdata[0]->user_searchid;

				$category_ids=$userdata[0]->category_ids;
					if($category_ids=="")
					{
					$category_ids=0;	
					}
					
					$geolocation=$latitude.",".$longitude;
					$qry="SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($category_ids) and `cm4_categories`.`type_id`=1";
					
					 $gettags= \DB::select($qry);
	
			if(count($gettags)>0)
			{
			$tags=$gettags[0]->tags;
			}	
				 	if($tags=="")
					{
					$tags="Others";	
					}
					if($contact_person=="")
					{
					$contact_person="";	
					}
					
					$update=array(
						'id' => $Doc_Id,
						"user_id" => array(
							'set' => $username
						),
						"call_time" => array(
							'set' => $call_time
						),
						
						"cc_fdail" => array(
							'set' => $cc_fdail
						),
						"live_status" => array(
							'set' => 1
						),
						"contact_person" => array(
							'set' => $contact_person
						),
						"user_name" => array(
							'set' => $user_name
						)
						,
						"contact_no" => array(
							'set' => $contact_no
						)
						,
						"latitude" => array(
							'set' => $latitude
						)
						,
						"longitude" => array(
							'set' => $longitude
						)
						,
						"geolocation" => array(
							'set' => $geolocation
						)
						,
						"category" => array(
							'set' => $category
						)
						,
						"address" => array(
							'set' => isset($address)?$address:""
						)
						,
						"locality" => array(
							'set' => isset($locality)?$locality:""
						)
						,
						"category_ids" => array(
							'set' => isset($category_ids)?$category_ids:"0"
						)
						,
						
						"service" => array(
							'set' => isset($category)?$category:""
						)
						,
						"profile_pic" => array(
							'set' => isset($profile_pic)?$profile_pic:""
						)
						,
						"tags" => array(
							'set' => isset($tags)?$tags:""
						),
						
						"user_searchid" => array(
							'set' => isset($user_searchid)?$user_searchid:""
						),
						
					);
					
				$update = json_encode(array($update));
					
				$ch = curl_init('http://172.16.200.35:8983/solr/premium_search/update?commit=true');
				
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);

				// Return transfert
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Set type of data sent
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				//echo "<pre>"; print_r($output);
				// Get response code
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($responseCode == 200)
				{
					return true;
				
				}
				else
				{
					return false;
					
				}
			}
		}
		
		
	 //Get user piggy bank ac Balance.
	 public function getpiggybalance() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
//return "hi";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('uid', $requestData)

        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 1) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
          
        }
     $uid=$requestData['uid'];
 $matchThese = ['id' => $requestData['uid']];

        $userInfo = CM4UserProfile::where($matchThese)->get(['contact_no']);
		$contact_no=$userInfo[0]->contact_no;
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$balance=$CreditInfo[0]->piggy_bal;
		  
		 \ DB::statement("UPDATE cm4_user_profile SET piggy_bal ='".$balance."' where id='".$uid."'");
		//$userInfo = CM4UserProfile::where($matchThese)->get(['piggy_bal']);
        
		if(count($CreditInfo)==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.109'),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109'), "device_key" => $token]);
            return $result;
        }	
	  $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $CreditInfo[0] , "device_key" => $token]);
	 return $data;
       }	
	
	/**
     *This Api to Create new Surviour.
     *
     * @return Response
     */
    public function addRatingReview() {
        //\Log::info('Add Ratting Reviews.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        
		if ($requestData['given_by_uid'] && $requestData['given_to_uid']) {
         
		
               $ratinggiventouid=$requestData['given_to_uid'];
               $length=strlen($requestData['given_by_contact']);
               if($length==12){
               		$given_by_contact=$requestData['given_by_contact'];
               }else{
               		$given_by_contact='91'.$requestData['given_by_contact'];
               }

               $lengthto=strlen($requestData['given_to_contact']);
               if($lengthto==12){
               		$given_to_contact=$requestData['given_to_contact'];
               }else{
               		$given_to_contact='91'.$requestData['given_to_contact'];
               }

			   $data = [
                    'given_by_uid' =>$requestData['given_by_uid'],
                    'given_to_uid' => $requestData['given_to_uid'],
                    'given_by_contact' => $given_by_contact,
                    'given_to_contact' => $given_to_contact,
					'rating' => isset($requestData['rating'])?$requestData['rating']:'0',
                    'comments' =>isset($requestData['comments']) ? $requestData['comments'] :'',
					'type' =>isset($requestData['type']) ? $requestData['type'] :'',
                    ];
                //print_r($data);die;
                $data = CM4ReviewRating::create($data);
                $id = ['id' =>$data->id];
               $getaveragequery="SELECT COALESCE(SUM(rating)/COUNT(*), 0) AS avgrating  FROM `cm4_rating_review` WHERE rating >0 and given_to_uid='".$ratinggiventouid."'";
			   $getaverage= \ DB::select( $getaveragequery);
			    $totalavg=$getaverage[0]->avgrating;
			   
			    \ DB::statement("UPDATE cm4_user_profile SET user_rating ='".$totalavg."' where id='".$ratinggiventouid."'");
			   
			   
			   $result = collect(["status" => "1", "message" => 'Successfully Submited..', 'errorCode' => '', 'errorDesc' => '', "data" => $id]);
                return $result;
            } 
			else {
                 $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "data" => array()]);
            return $result;
            }
        }
	
		//GET STATUS FOR OUTGOING CALLS
		/**
     *
     * @return Response
     */

    public function outgoingcallstatus() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        $outgoingsuccess=0;
	$callercontact=$requestData['callercontactno'];
	$callecontact=$requestData['callecontactno'];
		$data =\DB::connection('a2billing')->table('cc_card')
            ->where('phone','=',$callercontact)
            ->get();
	  if (count($data) > 0){
            $user_id = $data[0]->id;
          $mobilenumber=$data[0]->phone;
          $caller_card_id=$data[0]->id;
	
        }else {
			
          $user_id = 0;
      }

        if($user_id !=0 && $mobilenumber!='')
        {
			$data =\DB::connection('a2billing')->table('cc_call')
                ->where('calledstation', '=',$callecontact)
                ->Where('card_id', '=', $caller_card_id)
                ->orderBy('id', 'desc')
                ->take(1)
                ->get();
			
			if(count($data)>0)
			{
			
			foreach($data as $val)
			{ 
			if($val->terminatecauseid==1)
			{
			$outgoingsuccess=1;	
			}
			
			}
		}
		 
    }
       $data=array();
        if($outgoingsuccess==1)
        {
   $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $data
                , "device_key" => $token]);
        }
        else{
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
        }
 return response()->json($data, 200);

    }		
	
//API for Get User Ads
		 public function GetAdsForUser() {
        //\Log::info('GetAdsForUser.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        $uid=$requestData['uid'];
		$latitude=$requestData['latitude'];
		$longitude=$requestData['longitude'];
		$usercontact=array();
		$savecontact=array();
		$friendspreid=array();
	
				//CHECK EXISTING ADS OF USERS IN cm4_generated_ads
				$matchThese = ['my_id' =>$uid];
				$getads = cm4SaveAdData::where($matchThese)->get();
				$adscount = $getads->count();
				if($adscount==0)
				{

	//Get Ad data maching with users phone book and with the data send from Sandeep Sir..
	$getusersforad="SELECT cup.contact_no,up.id,up.user_name,trim(up.contact_person) as contact_person,up.data_source,up.profile_pic,up.is_installed,up.user_rating,up.category_ids,up.locality,up.category FROM `cm4_user_phonebook` cup,`cm4_user_profile` up WHERE cup.contact_no=up.contact_no AND cup.uploader_id='".$uid."' AND up.data_source='1' AND category_ids!='0'";
			$userdata= \ DB::select($getusersforad);
 			if(!empty($userdata))
				{
				foreach($userdata as $userarray)
				{
				$contact_profile_pic="";
				$num=$userarray->contact_no;	
				$contact_person=$userarray->contact_person;
				$friendspreid[]=$userarray->category_ids;
				if($contact_person=="")
				{
				$contact_person=$userarray->user_name;
				}
				 if (preg_match("/;/",$userarray->category)) {
			$category=explode(';',$userarray->category);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
			}
			} else {
		$category=explode(':',$userarray->category);
		$searchtext[]=$category[0];
		}
	   //$categorytext=implode(",",$searchtext);
		$categorytext=$searchtext[0];
		$string = str_replace('&', '', $categorytext);
		$categorytext=strtolower(str_replace(' ', '',$string));	
		
				 //path to directory to scan
    $directory = "/var/www/html/uploaded_file/adimage/";
    //get all image files with a .jpg extension. This way you can add extension parser
    $images = glob($directory."$categorytext*.{jpg,png}", GLOB_BRACE);
    $listImages=array();
    $imagename="";
	foreach($images as $image){
        $imagename=basename($image);
		 }
	if($imagename!="")
	{
	$category_image="$imagename";	
	}
	else
	{
	$category_image="dummy_1.jpg";	
	}
	
				$category=$userarray->category;		
				$category_ids=$userarray->category_ids;
				$calls=rand(5,150);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$userarray->is_installed,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$userarray->id,'category'=>$category,'category_ids'=>$category_ids,'user_rating'=>$userarray->user_rating,'categorytext'=>$categorytext,'category_image'=>$category_image,'calls'=>$calls,'locality'=>$userarray->locality));	
				unset($searchtext);
				}
				}	
		
		$getidsforsearch="";
		$newcondition="";
		if(!empty($friendspreid))
		{
		$idtext=implode(",",$friendspreid);
		$idarray=explode(",",$idtext);
		$getidsforsearch="AND (";
		$getidsend=")";
		$getidsforsr="";
		foreach($idarray as $ids)
		{
		$getidsforsr.="OR (FIND_IN_SET($ids, category_ids)) ";	
		}
		//echo $getidsforsearch;die;
		$text=trim($getidsforsr,"OR");
		$newcondition=$getidsforsearch.$text.$getidsend;
		}
		
		if($newcondition!="")
		{
		//FOR RECOMMENDATIONS
		$query="SELECT id as uid, profile_pic,category,category_ids,is_installed,trim(contact_person) as contact_person,user_name,user_rating,locality,contact_no,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(latitude))* COS( RADIANS(longitude) - RADIANS($longitude)) + SIN ( RADIANS( $latitude)) * SIN(RADIANS(latitude )))) AS distance  FROM cm4_user_profile  WHERE   data_source='1' $newcondition GROUP BY contact_no HAVING distance<6 LIMIT 50";	
		}	
		else
		{
			//FOR RECOMMENDATIONS
		$query="SELECT id as uid, profile_pic,category,category_ids,is_installed,trim(contact_person) as contact_person,user_name,user_rating,locality,contact_no,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(latitude))* COS( RADIANS(longitude) - RADIANS($longitude)) + SIN ( RADIANS( $latitude)) * SIN(RADIANS(latitude )))) AS distance  FROM cm4_user_profile  WHERE   data_source='1' and category_ids!=0 GROUP BY contact_no HAVING distance<6 LIMIT 50";
		}
		
		$data= \ DB::select($query);
		if(count($data)>0)
		{	
		foreach($data as $val){
            $contact_profile_pic="";
				$num=$val->contact_no;	
			$contact_person=$val->contact_person;
				if($contact_person=="")
				{
				$contact_person=$val->user_name;
				}

			if($val->profile_pic!="") {
                $val->profile_pic = "https://www.callme4.com:8443/uploaded_file/user_pic/" . $val->profile_pic;
            }else{
                $val->profile_pic ='';
            }
	
	
	   if (preg_match("/;/",$val->category)) {
			$category=explode(';',$val->category);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
				}
		} else {
		$category=explode(':',$val->category);
		$searchtext[]=$category[0];
		}
	   //$categorytext=implode(",",$searchtext);
		$categorytext=$searchtext[0];
		$string = str_replace('&', '', $categorytext);
		$categorytext=strtolower(str_replace(' ', '',$string));	
	  //path to directory to scan
    $directory = "/var/www/html/uploaded_file/adimage/";
    //get all image files with a .jpg extension. This way you can add extension parser
    $images = glob($directory."$categorytext*.{jpg,png}", GLOB_BRACE);
    $listImages=array();
    $imagename="";
	foreach($images as $image){
        $imagename=basename($image);
		 }
	if($imagename!="")
	{
	$category_image="$imagename";	
	}
	else
	{
	$category_image="dummy_1.jpg";	
	}
	$category=$val->category;		
	$category_ids=$val->category_ids;
	$calls=rand(5,150);
array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$val->is_installed,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$val->uid,'category'=>$category,'category_ids'=>$category_ids,'user_rating'=>$val->user_rating,'categorytext'=>$categorytext,'category_image'=>$category_image,'calls'=>$calls,'locality'=>$val->locality));	
	
	unset($searchtext);

	   }
		}
		else
    {
		$query="SELECT id as uid, profile_pic,category,category_ids,locality,is_installed,trim(contact_person) as contact_person,user_name,user_rating,contact_no,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(callme.cm4_user_profile.latitude))* COS( RADIANS(longitude) - RADIANS($longitude)) + SIN ( RADIANS( $latitude)) * SIN(RADIANS(latitude)))) AS distance  FROM cm4_user_profile  WHERE   data_source='1' AND category_ids!='0' GROUP BY contact_no  LIMIT 10";
	
        $data= \ DB::select($query);
		foreach($data as $val){
            $contact_profile_pic="";
				$num=$val->contact_no;	
			$contact_person=$val->contact_person;
				if($contact_person=="")
				{
				$contact_person=$val->user_name;
				}
		
			if($val->profile_pic!="") {
                $val->profile_pic = "https://www.callme4.com:8443/uploaded_file/user_pic/" . $val->profile_pic;
            }else{
                $val->profile_pic ='';
            }
       
	   if (preg_match("/;/",$val->category)) {
			$category=explode(';',$val->category);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
				}
		} else {
		$category=explode(':',$val->category);
		$searchtext[]=$category[0];
		}
	   //$categorytext=implode(",",$searchtext);
		$categorytext=$searchtext[0];
		$string = str_replace('&', '', $categorytext);
		$categorytext=strtolower(str_replace(' ', '',$string));	
	    //path to directory to scan
    $directory = "C:/xampp/htdocs/uploaded_file/adimage/";
    //get all image files with a .jpg extension. This way you can add extension parser
    $images = glob($directory."$categorytext*.{jpg,png}", GLOB_BRACE);
    $listImages=array();
    $imagename="";
	foreach($images as $image){
        $imagename=basename($image);
		 }
		if($imagename!="")
	{
	$category_image="$imagename";	
	}
	else
	{
	$category_image="dummy_1.jpg";	
	}
	$category=$val->category;		
	$category_ids=$val->category_ids;
	$calls=rand(5,150);
	array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$val->is_installed,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$val->uid,'category'=>$category,'category_ids'=>$category_ids,'user_rating'=>$val->user_rating,'categorytext'=>$categorytext,'category_image'=>$category_image,'calls'=>$calls,'locality'=>$val->locality));	
	unset($searchtext);
		}
	}
		
		foreach($usercontact as $val)
		{
		$data = [
                    'my_id' =>$uid,
                    'uid' => $val['uid'],
                    'contact_no' => $val['contact_no'],
                    'contact_person' =>$val['contact_person'],
					'callme4_status'=>$val['callme4_status'],
					'contact_profile_pic'=>$val['contact_profile_pic'],
					'category_ids'=>$val['category_ids'],
					'user_rating'=>$val['user_rating'],
					'categorytext'=>$val['categorytext'],
					'category'=>$val['category'],
					'category_image'=>$val['category_image'],
					'calls'=>$val['calls'],
					'locality'=>$val['locality'],
					'type_id'=>'5',
					'type'=>'Earnings Ads',
					'ad_type'=>'1'
					 ];	
			
		 $data = cm4SaveAdData::create($data);
		
		}
	}
	//************FETCH DATA FROM TABLES******************	
	
	$getdynamicdata="select uid,contact_no,contact_person,callme4_status,contact_profile_pic,category_ids,user_rating,categorytext,category,category_image,calls,type_id,type,ad_type,locality from cm4_generated_ads where my_id='".$uid."' ORDER BY RAND() LIMIT 0,10";
		$dynamicaddata= \ DB::select($getdynamicdata);
		
		if(!empty($dynamicaddata))
				{
				foreach($dynamicaddata as $adsdyarray)
				{
				 $adsdyarray->category_image = \Config::get('constants.results.root')."/adimage/".$adsdyarray->category_image;
				if($adsdyarray->contact_profile_pic != "")					
				$adsdyarray->contact_profile_pic = \Config::get('constants.results.root')."/adimage/".$adsdyarray->contact_profile_pic;
				
				array_push($savecontact,array('contact_no'=>$adsdyarray->contact_no,'contact_person'=>$adsdyarray->contact_person,'callme4_status'=>$adsdyarray->callme4_status,'contact_profile_pic'=>$adsdyarray->contact_profile_pic,'uid'=>$adsdyarray->uid,'category'=>$adsdyarray->category,'category_ids'=>$adsdyarray->category_ids,'user_rating'=>$adsdyarray->user_rating,'categorytext'=>$adsdyarray->categorytext,'category_image'=>$adsdyarray->category_image,'calls'=>$adsdyarray->calls,'ad_type'=>'1','type'=>'Earnings Ads','type_id'=>'5','locality'=>$adsdyarray->locality));	

				}
				}
		
		//GET STATIC ADS	
	$getusersstaticads="SELECT id as uid,adimage as category_image,adsmallimage as contact_profile_pic,type,type_id,'' as contact_no,'' as contact_person,'' as locality,0 as callme4_status,0 as category_ids,0 as user_rating,'' as categorytext,'' as category,0 as calls,0 as ad_type from cm4_feeds where type_id='5'";
			$staticaddata= \ DB::select($getusersstaticads);
 			if(!empty($staticaddata))
				{
				foreach($staticaddata as $adsarray)
				{
				 
			$adsarray->category_image = \Config::get('constants.results.root')."/adimage/".$adsarray->category_image;
			$adsarray->contact_profile_pic = \Config::get('constants.results.root')."/adimage/".$adsarray->contact_profile_pic;		

				 array_push($savecontact,array('contact_no'=>$adsarray->contact_no,'contact_person'=>$adsarray->contact_person,'callme4_status'=>$adsarray->callme4_status,'contact_profile_pic'=>$adsarray->contact_profile_pic,'uid'=>$adsarray->uid,'category'=>$adsarray->category,'category_ids'=>$adsarray->category_ids,'user_rating'=>$adsarray->user_rating,'categorytext'=>$adsarray->categorytext,'category_image'=>$adsarray->category_image,'calls'=>$adsarray->calls,'ad_type'=>'0','locality'=>$adsarray->locality,'type'=>'Earnings Ads','type_id'=>'5'));	
				}
				}
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" =>$savecontact]);
                return $result;	
		}
	
	
	//****************************************NEWLY ADDED********************************************//
	//NEW ADSFOR USERS
		 public function GetAdsNewForUser() {
        //\Log::info('GetAdsForUser.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
   
		$uid=$requestData['uid'];
		$latitude=$requestData['latitude'];
		$longitude=$requestData['longitude'];
		$usercontact=array();
		$savecontact=array();
		$friendspreid=array();
	
				//CHECK EXISTING ADS OF USERS IN cm4_generated_ads
				$matchThese = ['my_id' =>$uid];
				
				$getads = cm4SaveAdData::where($matchThese)->get();
				$adscount = $getads->count();
				
				if($adscount!=0)
				{
				$getdynamicdata="select uid,contact_no,contact_person,callme4_status,contact_profile_pic,category_ids,user_rating,categorytext,category,category_image,calls,type_id,type,ad_type,locality,'0' as static_uid,'' as static_text,'0' as youtube_url from cm4_generated_ads where my_id='".$uid."' ORDER BY RAND() LIMIT 0,1";
				}
				else
				{
				$getdynamicdata="select uid,contact_no,contact_person,callme4_status,contact_profile_pic,category_ids,user_rating,categorytext,category,category_image,calls,type_id,type,ad_type,locality,'0' as static_uid,'' as static_text,'0' as youtube_url from cm4_generated_ads  ORDER BY RAND() LIMIT 0,1";	
				}	
	//************FETCH DATA FROM TABLES******************	
	
	$dynamicaddata= \ DB::select($getdynamicdata);
		
		if(!empty($dynamicaddata))
				{
				foreach($dynamicaddata as $adsdyarray)
				{
				 $adsdyarray->category_image = \Config::get('constants.results.root')."/adimage/".$adsdyarray->category_image;
				if($adsdyarray->contact_profile_pic != "")					
				$adsdyarray->contact_profile_pic = \Config::get('constants.results.root')."/adimage/".$adsdyarray->contact_profile_pic;
				
				array_push($savecontact,array('contact_no'=>$adsdyarray->contact_no,'contact_person'=>$adsdyarray->contact_person,'callme4_status'=>$adsdyarray->callme4_status,'contact_profile_pic'=>$adsdyarray->contact_profile_pic,'uid'=>$adsdyarray->uid,'category'=>$adsdyarray->category,'category_ids'=>$adsdyarray->category_ids,'user_rating'=>$adsdyarray->user_rating,'categorytext'=>$adsdyarray->categorytext,'category_image'=>$adsdyarray->category_image,'calls'=>$adsdyarray->calls,'ad_type'=>'1','type'=>'Earnings Ads','type_id'=>'5','locality'=>$adsdyarray->locality,'static_uid'=>$adsdyarray->static_uid,'static_text'=>$adsdyarray->static_text,'youtube_url'=>$adsdyarray->youtube_url));	

				}
				}
		
		//GET STATIC ADS	
	$getusersstaticads="SELECT id as uid,adimage as category_image,adsmallimage as contact_profile_pic,type,type_id,'' as contact_no,'' as contact_person,'' as locality,0 as callme4_status,0 as category_ids,0 as user_rating,'' as categorytext,'' as category,0 as calls,0 as ad_type,static_uid,static_text from cm4_feeds where type_id='5' and id in(25,32,36,37)";
			$staticaddata= \ DB::select($getusersstaticads);
 			if(!empty($staticaddata))
				{
				foreach($staticaddata as $adsarray)
				{
			if($adsarray->uid==34)
			{
			$adsarray->youtube_url="https://www.youtube.com/channel/UCKLLLic0FeQfaynTMVnGQmA";	
			}		
			else
			{
			$adsarray->youtube_url="0";		
			}
			$adsarray->category_image = \Config::get('constants.results.root')."/adimage/".$adsarray->category_image;
			$adsarray->contact_profile_pic = \Config::get('constants.results.root')."/adimage/".$adsarray->contact_profile_pic;		

				 array_push($savecontact,array('contact_no'=>$adsarray->contact_no,'contact_person'=>$adsarray->contact_person,'callme4_status'=>$adsarray->callme4_status,'contact_profile_pic'=>$adsarray->contact_profile_pic,'uid'=>$adsarray->uid,'category'=>$adsarray->category,'category_ids'=>$adsarray->category_ids,'user_rating'=>$adsarray->user_rating,'categorytext'=>$adsarray->categorytext,'category_image'=>$adsarray->category_image,'calls'=>$adsarray->calls,'ad_type'=>'0','locality'=>$adsarray->locality,'type'=>'Earnings Ads','type_id'=>'5','static_uid'=>$adsarray->static_uid,'static_text'=>$adsarray->static_text,'youtube_url'=>$adsarray->youtube_url));	
				}
				}
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" =>$savecontact]);
                return $result;	
		}
	//END OF NEWUSERS ADS
	
	
	
	
	//END OF USERS ADS
	
	//API for syncusercontact
		 public function syncusercontact() {
        //\Log::info('Sync User Contact.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        $uid=$requestData['uid'];
		$usercontact=array();
		$savecontact=array();
		
		$count=0;
		foreach ($requestData['contacts'] as $value1)
				{
				$count++;
				$profile="";
				$profile_pic="";
				$contact_profile_pic="";
				$newString=str_replace(" ","",$value1['contact_no']);
				$number=preg_replace("/[^0-9+,.]/", "", $newString); 
				if(strlen($number)>=10)
				{
				$num=substr($number, -10);
				
				$contact_person=$value1['contact_person'];
				$profile_pic=$value1['contact_profile_pic'];
				 if($value1['contact_profile_pic']!="")
				 {
				$data=$this->imagephonebook($value1['contact_profile_pic'],$count);	 
				 $profile=$data['name'];
				 $contact_profile_pic=\Config::get('constants.results.root')."/user_pic/" .$profile;
				 }
				 else
				 {
					$profile_pic="";
					$contact_profile_pic="";
					$profile="";
				 }
				
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get();
				$Profilestatus = $user->count();
				array_push($savecontact,array('uploader_id'=>$uid,'contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus));
				 
			$matchThese = ['contact_no' =>$num,'uploader_id' =>$uid];
				$contactquery = CM4Userphonebook::where($matchThese)->get();
				$duplicatecount = $contactquery->count();
				
				if($duplicatecount==0)
				{	
			$data = [
                    'uploader_id' =>$uid,
                    'contact_no' => $num,
                    'contact_person' => $contact_person,
                    'callme4_status' =>$Profilestatus,
					'profile_pic'=>$profile
					 ];
				  
				  $data = CM4Userphonebook::create($data);
			
				}
				$id="";
				if($user->count()>0)
				{
				$id=$user[0]['id'];	
				}
				
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id));
				}
		}
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" => $usercontact]);
                return $result;	
		
		}
	
	//API for My-Network
		 public function MyNetwork2() {
        //\Log::info('Sync User Contact.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        $uid=$requestData['uid'];
		$usercontact=array();
		$savecontact=array();
		$count=0;
		$earnedamt="0.00";
			$details_url="http://172.16.200.35:8983/solr/phonebook/select?q=*%3A*&fq=(uploader_id:$uid)&wt=json&indent=true&start=0&rows=1000";
		$details_url=preg_replace('!\s+!', '+', $details_url);
        $response= file_get_contents($details_url);
        $response=json_decode($response, true);
        $response_arrcount=$response["response"]["numFound"];
		$contact_profile_pic="";
		if($response_arrcount!=0)
		{
		$response_arr=$response["response"]["docs"];
 				if(!empty($response_arr))
				{
				foreach($response_arr as $userarray)
				{
				$num=$userarray['contact_no'];	
				$contact_person=$userarray['contact_person'];	
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get(['id','piggy_bal']);
				$Profilestatus = $user->count();
				$id="";
				if($Profilestatus=='1')
				{
				$id=$user[0]['id'];
				$earnedamt=number_format($user[0]['piggy_bal'],2);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				else
				{
				/* $Profilestatus='0';
				$earnedamt="0.00";	
				array_push($savecontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt)); */	
				}
				}
				}
			}
		else
		{
		if($requestData['contacts']!="")
		{
		foreach($requestData['contacts'] as $value1)
				{
				$count++;
				$profile="";
				$profile_pic="";
				$contact_profile_pic="";
				$newString=str_replace(" ","",$value1['contact_no']);
				$number=preg_replace("/[^0-9+,.]/", "", $newString); 
				if(strlen($number)>=10)
				{
				$num=substr($number, -10);
				$contact_person=utf8_encode($value1['contact_person']);
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get(['id','piggy_bal']);
				$Profilestatus = $user->count();
			$data = [
                    'uploader_id' =>$uid,
                    'contact_no' => $num,
                    'contact_person' => $contact_person,
                    'callme4_status' =>$Profilestatus,
					'profile_pic'=>$profile
					 ];
				 $data = CM4Userphonebook::create($data);
			     $docid=$data->id;
			$update=array(
						'id' => $docid,
						"uploader_id" => array(
							'set' => $uid
						),
						"callme4_status" =>array(
							'set' => $Profilestatus
						),
						
						"contact_person" => array(
							'set' =>$contact_person
						),
						"contact_no" => array(
							'set' =>$num
						),
						"profile_pic" => array(
							'set' =>''
						)
					);
				$update = json_encode(array($update));
				$ch = curl_init('http://172.16.200.35:8983/solr/phonebook/update?commit=true');
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			
			if($Profilestatus=='1')
				{
				$id=$user[0]['id'];
				$earnedamt=number_format($user[0]['piggy_bal'],2);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				else
				{
				/* $id="";
				$Profilestatus='0';
				$earnedamt="0.00";	
				array_push($savecontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	 */
				}
			}
		  }
		}
		}
	$usercontactres=array_merge($usercontact,$savecontact);
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" =>$usercontact]);
                return $result;	
		}
	
	
	
	//API for My-Network3
		 public function MyNetwork3() {
        //\Log::info('Sync User Contact.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        $uid=$requestData['uid'];
		$usercontact=array();
		$savecontact=array();
		$count=0;
		$earnedamt="0.00";
			$details_url="http://172.16.200.35:8983/solr/phonebook/select?q=*%3A*&fq=(uploader_id:$uid)&wt=json&indent=true&start=0&rows=1000";
		$details_url=preg_replace('!\s+!', '+', $details_url);
        $response= file_get_contents($details_url);
        $response=json_decode($response, true);
        $response_arrcount=$response["response"]["numFound"];
		$contact_profile_pic="";
		if($response_arrcount!=0)
		{
		$response_arr=$response["response"]["docs"];
 				if(!empty($response_arr))
				{
				foreach($response_arr as $userarray)
				{
				$num=$userarray['contact_no'];	
				$contact_person=$userarray['contact_person'];	
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get(['id','piggy_bal']);
				$Profilestatus = $user->count();
				$id="";
				if($Profilestatus=='1')
				{
				$id=$user[0]['id'];
				$earnedamt=number_format($user[0]['piggy_bal'],2);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				else
				{
				/* $Profilestatus='0';
				$earnedamt="0.00";	
				array_push($savecontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt)); */	
				}
				}
				}
			}
		else
		{
		if($requestData['contacts']!="")
		{
		foreach($requestData['contacts'] as $value1)
				{
				$count++;
				$profile="";
				$profile_pic="";
				$contact_profile_pic="";
				$newString=str_replace(" ","",$value1['contact_no']);
				$number=preg_replace("/[^0-9+,.]/", "", $newString); 
				if(strlen($number)>=10)
				{
				$num=substr($number, -10);
				$contact_person=utf8_encode($value1['contact_person']);
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get(['id','piggy_bal']);
				$Profilestatus = $user->count();
			$data = [
                    'uploader_id' =>$uid,
                    'contact_no' => $num,
                    'contact_person' => $contact_person,
                    'callme4_status' =>$Profilestatus,
					'profile_pic'=>$profile
					 ];
				 $data = CM4Userphonebook::create($data);
			     $docid=$data->id;
			$update=array(
						'id' => $docid,
						"uploader_id" => array(
							'set' => $uid
						),
						"callme4_status" =>array(
							'set' => $Profilestatus
						),
						
						"contact_person" => array(
							'set' =>$contact_person
						),
						"contact_no" => array(
							'set' =>$num
						),
						"profile_pic" => array(
							'set' =>''
						)
					);
				$update = json_encode(array($update));
				$ch = curl_init('http://172.16.200.35:8983/solr/phonebook/update?commit=true');
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			
			if($Profilestatus=='1')
				{
				$id=$user[0]['id'];
				$earnedamt=number_format($user[0]['piggy_bal'],2);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				else
				{
				/* $id="";
				$Profilestatus='0';
				$earnedamt="0.00";	
				array_push($savecontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	 */
				}
			}
		  }
		}
		}
	$usercontactres=array_merge($usercontact,$savecontact);
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" =>$usercontactres]);
                return $result;	
		}
	
	//API for My-Network
		 public function MyNetwork1() {
        //\Log::info('Sync User Contact.');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        $uid=$requestData['uid'];
		$usercontact=array();
		$savecontact=array();
		$count=0;
		$earnedamt="0.00";
		if($requestData['contacts']!="")
		{
		foreach($requestData['contacts'] as $value1)
				{
				$count++;
				$profile="";
				$profile_pic="";
				$contact_profile_pic="";
				$newString=str_replace(" ","",$value1['contact_no']);
				$number=preg_replace("/[^0-9+,.]/", "", $newString); 
				if(strlen($number)>=10)
				{
				$num=substr($number, -10);
				$contact_person=$value1['contact_person'];
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get();
				$Profilestatus = $user->count();
 $details_url = "http://172.16.200.35:8983/solr/phonebook/select?q=*%3A*&fq=(contact_no%3A$num%20AND%20uploader_id:$uid)&wt=json&indent=true";
		$details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);
        $response_arr= $response["response"]["docs"];
 				if(empty($response_arr))
				{	
			$data = [
                    'uploader_id' =>$uid,
                    'contact_no' => $num,
                    'contact_person' => $contact_person,
                    'callme4_status' =>$Profilestatus,
					'profile_pic'=>$profile
					 ];
				 $data = CM4Userphonebook::create($data);
			     $docid=$data->id;
			$update=array(
						'id' => $docid,
						"uploader_id" => array(
							'set' => $uid
						),
						"callme4_status" =>array(
							'set' => $Profilestatus
						),
						
						"contact_person" => array(
							'set' =>$contact_person
						),
						"contact_no" => array(
							'set' =>$num
						),
						"profile_pic" => array(
							'set' =>''
						)
					);
				$update = json_encode(array($update));
				$ch = curl_init('http://172.16.200.35:8983/solr/phonebook/update?commit=true');
				curl_setopt($ch, CURLOPT_POST,true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				$output = json_decode(curl_exec($ch));
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}
				$id="";
				
			}
		}
		}
		$details_url="http://172.16.200.35:8983/solr/phonebook/select?q=*%3A*&fq=(uploader_id:$uid)&wt=json&indent=true&start=0&rows=1000";
		$details_url=preg_replace('!\s+!', '+', $details_url);
        $response= file_get_contents($details_url);
        $response=json_decode($response, true);
        $response_arr=$response["response"]["docs"];
 				if(!empty($response_arr))
				{
				foreach($response_arr as $userarray)
				{
				$num=$userarray['contact_no'];	
				$contact_person=$userarray['contact_person'];	
				$matchThese = ['contact_no' =>$num];
				$user = CM4UserProfile::where($matchThese)->get();
				$Profilestatus = $user->count();
				$id="";
				if($Profilestatus=='1')
				{
				$id=$user[0]['id'];
				$earnedamt=number_format($user[0]->piggy_bal,2);
				array_push($usercontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				else
				{
				$Profilestatus='0';
				$earnedamt="0.00";	
				array_push($savecontact,array('contact_no'=>$num,'contact_person'=>$contact_person,'callme4_status'=>$Profilestatus,'contact_profile_pic'=>$contact_profile_pic,'uid'=>$id,'piggy_bal'=>$earnedamt));	
				}
				}
				}	
		$usercontactres=array_merge($usercontact,$savecontact);
		$result = collect(["status" => "1", "message" => 'Contact List..', 'errorCode' => '', 'errorDesc' => '', "data" =>$usercontactres]);
                return $result;	
		}
	
	
	// Register user from phone book to direct search and call.
    
	public function invitefromphonebook() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
  if (Request::header('content-type') == "application/json") {
		$requestData = Request::json()->all();
		} else 
		{
		$requestData = Request::all();
        }
	 if (!(array_key_exists('contact_no', $requestData)
            && array_key_exists('contact_person', $requestData)
        )) 
		{
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
       
        $fields = [
            'phone' => $requestData['contact_no'],
            'contact_person' => $requestData['contact_person'],
        ];
        $rules = [
            'phone' => 'required',
            'contact_person' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
			
			$contact_no=$requestData['contact_no'];
			//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
        $matchThese = ['contact_no' => $requestData['contact_no']];

        $user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();
    
           if($status==0){
				
				$phone=$requestData['contact_no'];
				$email=array_key_exists('email',$requestData)?$requestData['email']:"";
				$name=$requestData['contact_person'];
			
                $genData= $this->register($phone,$email,$name);
			
                $userId=$genData['user_id'];
                $fdial=$genData['cc_fdial'];
                $pass=$genData['cc_password'];
				$referal="";
				
				 if($requestData['profile_pic']!="")
				 {
				$data=$this->imageUpload($requestData['profile_pic']);	 
				 $profile_pic=$data['name'];
				 }
				 else
				 {
					$profile_pic=''; 
				 }
				 
				 
					if($requestData["address_source"]==1)
						   {//gps
                    $rec_addressgps=$requestData["location"]["address"];
						 $latitude = $requestData["location"]['latitude'];
                  $longtitude = $requestData["location"]['longitude'];  
						   }
				
				if($requestData["address_source"]==2)//manual
				{
				   $rec_address=$requestData["location"]["city"].
                        " ".$requestData["location"]["state"].
                        " ".$requestData["location"]["country"].
                        " ".$requestData["location"]["pincode"];

        //$latLng=$this->get_lat_long($rec_address);
			
		$latitude = $requestData["location"]["latitude"];
        $longtitude = $requestData["location"]["longitude"];
			
			
			}
				 $username = $requestData['contact_person'];
      
            $profilePic = $profile_pic;
            $lat = $latitude;
            $long = $longtitude;
            $addressSource = $requestData['address_source'];
            $address = $requestData['location']['address'];
            $address2 = $requestData['location']['address2'];
            $locality = $requestData['location']['locality'];
            $city = $requestData['location']['city'];
            $state = $requestData['location']['state'];
            $country = $requestData['location']['country'];
            $pincode = $requestData['location']['pincode'];
			$profession = $requestData['profession'];
          if($othersprofession=="")
				{
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				}
				else
				{
				$getcategory=$requestData['others'];
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$requestData['others'];
				}		
			  }
				$category_json=serialize($profession);
				}
				$address=$address."|".$address2;
			$uploader_id=$requestData['uploader_id'];
				
		$userinfo=array('user_id'=>$userId,'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$phone,'email'=>'','contact_person'=>$username,'about_us'=>'','city'=>$city,'state'=>$state,'locality'=>$locality,'address'=>$address,'category'=>$getcategory,'category_ids'=>$list,'data_source'=>'5','profile_pic'=>$profile_pic,'call_time'=>'','latitude'=>$lat,'longitude'=>$long,'cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'0','created_at'=>date('Y-m-d H:i:s'),'pincode'=>$pincode,'referal_code'=>$uploader_id,'is_installed'=>'0');
				
				if($profile_pic!="")
				{
				$profile_pic=\Config::get('constants.results.root')."/user_pic/" . $profile_pic;	
				}
				else
				{
				$profile_pic="";	
				}
				 $data=CM4UserProfile::create($userinfo);
			  
				//SEND SMS TO THE INVITED USERS
				
					$contact_person=$requestData['uploader_name'];
	
				$sendsms=$this->sendsmsfromphonebook($contact_person,$phone);
			  //working
	        $finalData=['user_registration_status'=>"0",'user'=>[
                    'id'=>$data->id,
                    'user_id'=>$userId,
                    'name'=>'',
                    'profile_pic'=>$profile_pic,
                    'gender'=>'',
                    'locality'=>$locality,
                    'age'=>'',
                    'address'=>$address,
                    'country'=>'',
                    'city'=>$city,
                    'state'=>$state,
                    'latitude'=>$lat,
                    'longitude'=>$long,
                    'call_time'=>'',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>$username,
                    'contact_no'=>$contact_no,
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>'',
                    'cc_password'=>$pass,
                    'email'=>'',
                    'cc_fdail'=>$fdial,
                    'category'=>$getcategory,
                    'piggy_bal'=>0,
                    'live_status'=>0,
                    'referal_code'=>'',
                    'update_profile_status'=>0,
                    'data_source'=>'5',
					'service_ids'=>$requestData['profession'],
					'favourite_status'=>'1'
                ]];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
			return $result;
		    }else{

			 $contact_no=$requestData['contact_no'];
		         
                    $time =explode('|',$user[0]['call_time']);
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device'] = "1";

                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = "" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['start_time']=$time[0];
                    $user[0]['end_time']=isset($time[1])?$time[1]:"";

                    $user[0]['service']=$user[0]['category'];
                    $user[0]['service_ids']=unserialize($user[0]['category_json']);
                 
                    unset($user[0]['category_ids']);
                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?0:1,'user'=>$user[0]];
                   $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
               return $result;
				}

		}
	
	
		//SEND SMS TO THE USERS WHO HAVE INVITED FROM PHONEBOOK 
	  public function sendsmsfromphonebook($contact_person,$phone)
	  {
		 //Working
        $ch = curl_init();
        $user="eshan@virtualemployee.com:v1rtual";
        $receipientno=$phone;
		$senderID="CALLME";
        $msgtxt="Requested link  bit.ly/callme4 by $contact_person for CallMe4 app.";
       curl_setopt($ch,CURLOPT_URL,  "http://api.mVaayoo.com/mvaayooapi/MessageCompose");
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&senderID=$senderID&receipientno=$receipientno&msgtxt=$msgtxt");
        $buffer = curl_exec($ch);
        if(empty ($buffer))
        { echo " buffer is empty ";
            }
        else
        { //echo $buffer;
            }
        curl_close($ch);
			return;
	  }
	
	
	/*************GET USER DETAILS FROM DATABASE BY ID*************/
	 public function getuserdetailsbyid() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
     $userId=$requestData['uid'];
        
		$lat=array_key_exists('latitude',$requestData)?$requestData['latitude']:"28.542689";
		$long=array_key_exists('longitude',$requestData)?$requestData['longitude']:"77.4033364";
		
		if($lat=="" || $long=="")
		{
		$lat="28.542689";
		$long="77.4033364";	
		}
		
    $myid=array_key_exists('myid',$requestData)?$requestData['myid']:"";
	
	$selectqry=\ DB::select("select id,contact_person,gender,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,email,call_time,per_min_val,locality,user_searchid,category as service,is_callback as online_status,(6371 * ACOS (COS(RADIANS(latitude))* COS(RADIANS($lat))* COS( RADIANS( $long ) - RADIANS(longitude)) + SIN ( RADIANS(latitude) ) * SIN(RADIANS( $lat )))) AS dist from cm4_user_profile where id='".$userId."'");
		
		if(isset($selectqry[0])>0)
		{
		if($selectqry[0]->category_ids!="")
		{
		$tags=$selectqry[0]->category_ids;
		$selecttags=\ DB::select("SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($tags) and `cm4_categories`.`type_id`=1");	
	//	echo $selecttags[0]->tags;die;
		$selectqry[0]->tags=$selecttags[0]->tags;
		
		}
		else
		{
			$selectqry[0]->tags="";
		}		
			
	 if($selectqry[0]->call_time!="") 
	 {
            $time= $this->today_timing($selectqry[0]->call_time);
             $time=str_replace("-","|",$time);
			if($time=='Closed')
			{
			$selectqry[0]->today_timing='';	
			}
			else
			{
			$selectqry[0]->today_timing=$time;
            }
		}
			else
			{
                $selectqry[0]->today_timing="";
            }	
		if(trim($selectqry[0]->contact_person)!="")
			{
			$selectqry[0]->contact_person=trim($selectqry[0]->contact_person);	
			}
			else if(trim($selectqry[0]->user_name)!="" && trim($selectqry[0]->contact_person)=="") 
			{
				$selectqry[0]->contact_person = $selectqry[0]->user_name;
            }
			else
			{
				$selectqry[0]->contact_person="";
			}
             $matchThese=['uid'=> $myid,'favid'=>$userId,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $selectqry[0]->favourite_status=  $user->count()>0?1:0;
               //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$userId;
		$searched_contact=$selectqry[0]->contact_no;	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$selectqry[0]->reviewcount=$raterevqryex[0]->reviewcount;	
			$selectqry[0]->avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Force Rate Update
		$today_date=date('Y-m-d');
		
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$selectqry[0]->force_close='1';	
			}
			else
			{
			$selectqry[0]->force_close='0';	
			}
		
		//Get About Us 
		$aboutus="";
		 $getaboutus="SELECT more_about as about_us FROM `cm4_user_social_info` WHERE uid='".$searched_uid."'";
			$getaboutus_ex= \ DB::select($getaboutus);
			if(count($getaboutus_ex)>0)
			{
			$selectqry[0]->about_us=$getaboutus_ex[0]->about_us;	
			
			}
			else
			{
			$selectqry[0]->about_us=$aboutus;	
			}
		
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$selectqry[0]->callcount=$callcount;
		}

            if($selectqry[0]->profile_pic!='') {
                $selectqry[0]->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;
            }else{
                $selectqry[0]->profile_pic = "" ;
            }
		//Video Status
		$video_status=CM4PremiumUser::where('id', '=',$userId)->where('video_id', '!=','')->get();
		$selectqry[0]->is_video=0;
		$selectqry[0]->video_id='';
		$selectqry[0]->video_title='';
		$selectqry[0]->Is_Youtube='0';
		if($video_status->count()>0)
		{
		$selectqry[0]->is_video=1;
		$selectqry[0]->video_id=$video_status[0]->video_id;	
		$selectqry[0]->video_title=$video_status[0]->video_title;	
		$selectqry[0]->Is_Youtube=$video_status[0]->Is_Youtube;
		}
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$userId."'");	
		    if(count($selectofferrate)>0)
			{
			$selectqry[0]->offer_rate=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$selectqry[0]->offer_rate='';		
			}
  
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$selectqry[0], "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }
	
	//CONTACT US CM4_USERS
	 public function CM4UserFeadback() {
        //\Log::info('Update Surveyor Locations.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

       if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('contact_person', $requestData)&&array_key_exists('subject', $requestData)&&array_key_exists('comments', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		 $data = [
                "uid" => $requestData['uid'],
                "contact_no" => $requestData['contact_no'],
                "contact_person" =>$requestData['contact_person'],
                "subject" => $requestData['subject'],
				"comments" => $requestData['comments'],
				"app_version" => $requestData['app_version']
				
            ];
            $insertrec=CM4UserFeadback::create($data);  
			if($insertrec)
			{
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
			else
			{
			
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
    }
	
	  	/**
     * getCallStatus.
     *
     * @return Response
     */
    public function getCallStatus() {
        //\Log::info('Get Call Status.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

       if (!(array_key_exists('date_time',$requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
			$todaydate=date('Y-m-d');
			$datetime=date('Y-m-d H:i:s');
			$endTime = strtotime("-15 minutes", strtotime($datetime));
			$fetchtime=date('Y-m-d H:i:s',$endTime);
		
		$querycalltime="SELECT count(*) as totalcalls from cc_callback_spool where entry_time<='".$fetchtime."' and date(entry_time)='".$todaydate."'";
  $callgetquery= \ DB::connection('a2billing')->select($querycalltime);
		 $totalcalls=$callgetquery[0]->totalcalls;
		
		$queryforerror="SELECT count(*) as errorcalls from cc_callback_spool where entry_time<='".$fetchtime."' and date(entry_time)='".$todaydate."' and `status`!='SENT'";
	$callErrors= \ DB::connection('a2billing')->select($queryforerror);
		 $totalcallserror=$callErrors[0]->errorcalls;
		$errorper=(($totalcallserror)/$totalcalls)*100;
		
		$errorper=number_format($errorper, 2);
		$allquerycalltime="SELECT callerid,exten,entry_time,status from cc_callback_spool where entry_time<='".$fetchtime."' and date(entry_time)='".$todaydate."'";
  $getquery= \ DB::connection('a2billing')->select($allquerycalltime);
		
		
		if($errorper>20)
		{
		 $ch = curl_init();
        $user="eshan@virtualemployee.com:v1rtual";
        $phone='9650608967,9873851557,9716140844,9999975535,9958487838,9717103789,9560744144';
		$receipientno=$phone;
		$contact_person="LAXMI";
		$senderID="CALLME";
        
		$msgtxt="Dear Team,We are getting  problem with callme4 calls having error $errorper% for the Day.";
       $text=urlencode($msgtxt);
	  
	   curl_setopt($ch,CURLOPT_URL,  "http://api.mVaayoo.com/mvaayooapi/MessageCompose");
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_POSTFIELDS, "user=$user&senderID=$senderID&receipientno=$receipientno&msgtxt=$text");
        $buffer = curl_exec($ch);
		if(empty ($buffer))
        { echo " buffer is empty ";
            }
      
        curl_close($ch);

		}
		if($errorper)
			{
           
			$data = collect(["status" => "1","error_per"=>$errorper,"message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" =>$getquery,"device_key" => $token]);
			}
			else
			{
			
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','data'=>array(),"error_per"=>"",'errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
    }
	
	/**
     * Show the form for fetching the GetSuggestedUser.
     *
     * @return Response
     */

    public function GetSuggestedUser() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	if (!(array_key_exists('latitude', $requestData)
             &&array_key_exists('longitude', $requestData)
             &&array_key_exists('uid', $requestData)
        )) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
     
        $uid=$requestData['uid'];
        $latitude=$requestData['latitude'];
        $longitude=$requestData['longitude'];
		$query="SELECT sa2billing.cc_call.src,sa2billing.cc_call.calledstation,COUNT(sa2billing.cc_call.src) AS srccnt,callme.cm4_user_profile.profile_pic,callme.cm4_user_profile.category,callme.cm4_user_profile.contact_person,callme.cm4_user_profile.user_name,callme.cm4_user_profile.id,callme.cm4_user_profile.user_rating,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(callme.cm4_user_profile.latitude))* COS( RADIANS( callme.cm4_user_profile.longitude) - RADIANS($longitude)) + SIN ( RADIANS( $latitude)) * SIN(RADIANS( callme.cm4_user_profile.latitude )))) AS distance  FROM sa2billing.cc_call,callme.cm4_user_profile  WHERE  sa2billing.cc_call.calledstation=callme.cm4_user_profile.contact_no AND callme.cm4_user_profile.category!='' GROUP BY sa2billing.cc_call.calledstation HAVING sa2billing.cc_call.calledstation!='' AND distance<6 ORDER BY srccnt DESC LIMIT 10";
	
        $data= \ DB::select($query);
		if(count($data)>0)
		{	
		foreach($data as $val){
            
			if($val->contact_person=="" ||$val->contact_person==" ")
			{
			$val->contact_person=$val->user_name;	
			}	
		
		
			if($val->profile_pic!="") {
                $val->profile_pic = "https://www.callme4.com:8443/uploaded_file/user_pic/" . $val->profile_pic;
            }else{
                $val->profile_pic ='';
            }
        }
		}
		else
    {
		$query="SELECT sa2billing.cc_call.src,sa2billing.cc_call.calledstation,COUNT(sa2billing.cc_call.src) AS srccnt,callme.cm4_user_profile.profile_pic,callme.cm4_user_profile.category,callme.cm4_user_profile.contact_person,callme.cm4_user_profile.user_name,callme.cm4_user_profile.id,callme.cm4_user_profile.user_rating,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(callme.cm4_user_profile.latitude))* COS( RADIANS( callme.cm4_user_profile.longitude) - RADIANS($longitude)) + SIN ( RADIANS( $latitude)) * SIN(RADIANS( callme.cm4_user_profile.latitude )))) AS distance  FROM sa2billing.cc_call,callme.cm4_user_profile  WHERE  sa2billing.cc_call.calledstation=callme.cm4_user_profile.contact_no AND callme.cm4_user_profile.category!='' GROUP BY sa2billing.cc_call.calledstation HAVING sa2billing.cc_call.calledstation!=''  ORDER BY srccnt DESC LIMIT 10";
	
        $data= \ DB::select($query);
		foreach($data as $val){
            
			if($val->contact_person=="" ||$val->contact_person==" ")
			{
			$val->contact_person=$val->user_name;	
			}	
		
		
			if($val->profile_pic!="") {
                $val->profile_pic = "https://www.callme4.com:8443/uploaded_file/user_pic/" . $val->profile_pic;
            }else{
                $val->profile_pic ='';
            }
        }
	
	
	}
 if (count($data)!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $data, "device_key" => $token]);
        } else {
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
            }
  return response()->json($data, 200);
    }
	
	/**
     * Show the form for fetching the GetnearbyUser.
     *
     * @return Response
     */

    public function GetnearbyUser() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	if (!(array_key_exists('latitude', $requestData)
             &&array_key_exists('longitude', $requestData)
             &&array_key_exists('uid', $requestData)
        )) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'uid' => $requestData['uid'],
        ];
        $rules = [
            'uid' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
     
        $uid=$requestData['uid'];
        $latitude=$requestData['latitude'];
        $longitude=$requestData['longitude'];
		//$query="SELECT profile_pic,category,contact_person,user_name,id,user_rating,(6371 * ACOS (COS(RADIANS($latitude))* COS(RADIANS(latitude))* COS( RADIANS( longitude) - RADIANS($longitude)) + SIN (RADIANS( $latitude)) * SIN(RADIANS(latitude )))) AS distance  FROM cm4_user_profile  WHERE  category!='' group by id having distance<20  ORDER BY id DESC LIMIT 10";
	$query="SELECT category,category_ids FROM cm4_user_profile  WHERE id='$uid' and category!=''";
	 $data= \ DB::select($query);
	
		$filter_str="";
		$responsearray=array();
		$mycategory="";
		if(!empty($data))
		{
		foreach($data as $val){
			$mycategory=$val->category;
		   //array_push($responsearray,array('my_profession'=>$val->category));
			
			if (preg_match("/;/",$val->category)) {
			$category=explode(';',$val->category);
			foreach($category as $val)
			{
			$newsearchtext=explode(':',$val);
			$searchtext[]=$newsearchtext[1];
			
			}
		
		} else {
		$category=explode(':',$val->category);
		$searchtext[]=$category[1];
		}
			
		$searchtext=implode(',',$searchtext);
		$searchtextarray=explode(',',$searchtext);
		 $count=1;
		 foreach($searchtextarray as $value)
		 {
			if($count==1)
			$filter_str.=' category:"'.$value.'"';
			else
			$filter_str.=' OR category:"'.$value.'"';	
		 $count++;
		 }
			$filterval=urlencode($filter_str);
				
		}
	 $start=0;
	 $rows=10;
	 $details_url = "http://172.16.200.35:8983/solr/search/select?q=$filterval&start=$start&rows=$rows&fl=id,contact_person,profile_pic,user_id,user_name,service&wt=json&indent=true&fq=-id:$uid";
		 $details_url = preg_replace('!\s+!', '+', $details_url);
        $response    = file_get_contents($details_url);
        $response = json_decode($response, true);
		
        $response_arr= $response["response"]["docs"];
			
			foreach($response_arr as $newval)
			{
			if($newval['contact_person']=="" || $newval['contact_person']==" ")	
			{
			$newval['contact_person']=$newval['user_name'];
			}
		 if($newval['profile_pic']!='') {
                $newval['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $newval['profile_pic'];
            }
		$newval['category']=substr($newval['service'],0,50);
		$newval['user_rating']=0;
		array_push($responsearray,$newval);
		
		}
	}
 if (count($data)!=0) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','my_profession'=>$mycategory,'errorDesc'=>'',"data" =>$responsearray, "device_key" => $token]);
        } else {
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'','my_profession'=>$mycategory, "device_key" => $token]);
            }
  return response()->json($data, 200);
    }
	
	/**
     * CM4VideoShoot.
     *
     * @return Response
     */
    public function CM4VideoShoot() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       $created_date = date('Y-m-d : H:i:s');
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
		} else {
            $requestData = Request::all();
        }
		
		if(!(array_key_exists('contact_person', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('uid', $requestData)
            && array_key_exists('profession', $requestData)
            && array_key_exists('shoot_location',$requestData)
            && array_key_exists('type_of_video', $requestData)
            && array_key_exists('script_idea', $requestData)
            )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

         $matchThese = ['uid' => $requestData['uid']];
	$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
     	$user = CM4VideoShoot::where($matchThese)->get();
        $status = $user->count();
     if ( $status == 0) {
	      $uid= $requestData['uid'];
		  $username = $requestData['contact_person'];
		  $contact_no = $requestData['contact_no'];
         $profession = $requestData['profession'];
            $workPlace = $requestData['shoot_location'];
			$type_of_video = $requestData['type_of_video'];
            $script_idea = $requestData['script_idea'];
			$gender=$requestData['gender'];	
			$about=$requestData['about'];
			$fb_id=$requestData['fb_id'];
			$fb_email=$requestData['fb_email'];
			$fb_name=$requestData['fb_name'];
			$fb_profile_pic=$requestData['fb_profile_pic'];		
			$fb_birthday=$requestData['fb_birthday'];	
			if($othersprofession=="")
				{
				 
				 $list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($requestData['profession']);
				}
				else
				{
				$getcategory=array_key_exists('others',$value)?$value['others']:"";
				$list=0;
				$category_json="a:0:{}";
				}		
			$data = [
                     "uid" => $uid,
					"contact_person" => $username,
					"contact_no" => $contact_no,
					"profession"=>$getcategory,
					"profession_ids"=>$category_json,
					"shoot_location"=>$workPlace,
					"type_of_video"=>$type_of_video,
					"script_idea"=>$script_idea,
					"gender"=>$gender,
					"about"=>$about,
					"fb_id"=>$fb_id,
					"fb_name"=>$fb_name,
					"fb_email"=>$fb_email,
					"fb_profile_pic"=>$fb_profile_pic,
					"fb_birthday"=>$fb_birthday	
					];
			$user=CM4VideoShoot::create($data);	
			// FB DETAILS ENTRY
			$fbdata = [
                    "uid" => $uid,
					"contact_no" => $contact_no,
					"fb_id"=>$fb_id,
					"fb_name"=>$fb_name,
					"fb_email"=>$fb_email,
					"fb_profile_pic"=>$fb_profile_pic,
					"fb_birthday"=>$fb_birthday	
					];
	   $fbuser = CM4FbUsers::where($matchThese)->get();
        $fbstatus = $fbuser->count();
     if ($fbstatus == 0){
			$fbuserentry=CM4FbUsers::create($fbdata);	
					
					}
			
			$insertedId = $user->id;               
			 $shootDate=Date('d-M-y',strtotime("+10 days"));
			   $flag=1;               

				$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>array('insert_id'=>$insertedId,'shoot_date'=>$shootDate), "device_key" => $token]);
            } 
       else {
$result = collect(["status" => "0", "message" =>'Unable to Create Request.','errorCode'=>'','errorDesc'=>'','data'=>(object)array(),"device_key" => $token]);

       }

        return response()->json($result, 200);


    }
	
	/**
     * CM4FbLogin.
     *
     * @return Response
     */
    public function CM4FbLogin() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       $created_date = date('Y-m-d : H:i:s');
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
		} else {
            $requestData = Request::all();
        }
		
		if(!(array_key_exists('contact_no', $requestData)
            && array_key_exists('uid', $requestData)
            && array_key_exists('fb_id', $requestData)
            && array_key_exists('fb_name',$requestData)
            )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

         $matchThese = ['uid' => $requestData['uid']];
     	$user = CM4FbUsers::where($matchThese)->get();
        $status = $user->count();
     if ( $status == 0) {
	      $uid= $requestData['uid'];
		  $contact_no = $requestData['contact_no'];
			$fb_id=$requestData['fb_id'];
			$fb_email=$requestData['fb_email'];
			$fb_name=$requestData['fb_name'];
			$fb_profile_pic=$requestData['fb_profile_pic'];		
			$fb_birthday=$requestData['fb_birthday'];	
			$gender=array_key_exists('fb_gender',$requestData)?$requestData['fb_gender']:"";
	   
	   $fbdata = [
                    "uid" => $uid,
					"contact_no" => $contact_no,
					"fb_id"=>$fb_id,
					"fb_name"=>$fb_name,
					"fb_email"=>$fb_email,
					"fb_profile_pic"=>$fb_profile_pic,
					"fb_birthday"=>$fb_birthday,
					"fb_gender"=>$gender	
					];
	  
			$fbuserentry=CM4FbUsers::create($fbdata);	
			
			$insertedId = $fbuserentry->id;               
			$flag=1;               

				$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>array('insert_id'=>$insertedId), "device_key" => $token]);
            } 
       else {
$result = collect(["status" => "0", "message" =>'Unable to Create Request.','errorCode'=>'','errorDesc'=>'','data'=>(object)array(),"device_key" => $token]);

       }

        return response()->json($result, 200);


    }
	
	/**
     * Get Cm4CallsEarning.
     *
     * @return Response
     */
    public function Cm4GetCallsEarning() {
        //\Log::info('Get Calls Earning.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
  $created_date = \Carbon\Carbon::today();
  if (!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		$uid=$requestData['uid'];
		$matchThese = ['id' => $requestData['uid']];
		$userInfo = CM4UserProfile::where($matchThese)->get(['contact_no']);
		$contact_no=$userInfo[0]->contact_no;
		
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		
		//$qry="SELECT cast(piggy_bal as decimal(6,2)) as piggy_bal FROM `cm4_user_profile` WHERE id='".$uid."'";
			//$userInfo= \DB::select($qry);

	if(count($CreditInfo)=='1')
	{
	$piggybal=$CreditInfo[0]->piggy_bal;
		
		
		$query="SELECT call_type,contact_no,call_date,call_time,call_duration,banner1_duration,banner2_duration,contact_person,cast(banner1_earning as decimal(6,2)) as banner1_earning,cast(banner2_earning as decimal(6,2)) as banner2_earning,(CAST(banner1_earning AS DECIMAL(6,2))+CAST(banner2_earning AS DECIMAL(6,2))) AS banners_earning,group_id FROM cm4_ad_data  WHERE uid='$uid'";
		$data= \ DB::select($query);
	
	
	if($data)
			{
            $data = collect(["status" => "1","piggy_bal"=>$piggybal,"message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" =>$data, "device_key" => $token]);
			}
	else
			{
			
			 $data = collect([ "status" => "1","piggy_bal"=>$piggybal,"message" => 'No records Found.','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),'data'=>array(),"device_key" => $token]);	
			}
	}
			else
			{
			
			 $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','data'=>array(),'errorDesc'=>\Config::get('constants.results.105'),"device_key" => $token]);	
			}
   return response()->json($data, 200);
    }
	
	/**
     * Cm4Earningads.
     *
     * @return Response
     */
    public function Cm4Earningads() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       $created_date = date('Y-m-d : H:i:s');
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
		} else {
            $requestData = Request::all();
        }
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            &&array_key_exists('contact_no', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
     $userId=$requestData['uid'];
     $contact_no=$requestData['contact_no'];
	 $max_id=$requestData['max_id'];
	 $sizecount=$requestData['size'];
	$maxids=\DB::select("select max(id) as maxid,count(*) as size from cm4_feeds where type_id='5'"); 
	$maximumid=$maxids[0]->maxid;
	$sizedata=$maxids[0]->size;
	
	if($maximumid!=$max_id || $sizedata!=$sizecount)
	{
	
	$selectoffers=\ DB::select("select id,content,adimage,type,type_id,adsmallimage from cm4_feeds"); 
	 
	  $earningads=array();
	  if(isset($selectoffers[0])>0)
		{
		foreach($selectoffers as $value)
		{
		$value->adimage = \Config::get('constants.results.root')."/adimage/".$value->adimage;
		$value->adsmallimage = \Config::get('constants.results.root')."/adimage/".$value->adsmallimage;	
		if($value->type_id=='5')
		{
		array_push($earningads,$value);	
		}
		}
		 if(count($earningads)>0)
	  {
		  $result = collect(["status" => "1", "message" => 'Earning Ads', 'errorCode' => '', 'errorDesc' => '', "data" => $earningads]);
                return $result;
            }
		}
	}		
		else {
            $result = collect(["status" => "0", "message" => 'Already Fetched', 'errorCode' => '400', 'errorDesc' => 'Already Fetched', "data" => (object)array()]);
			 return $result;
	}
	}
	
	/****************************PROMTER API****************************************************************/
	 /**
     * Login Api.
     *
     * @return Response
     */
    public function user_login() {
		 $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //\Log::info('Login Api.');
        $collection = [];
	if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	
	if (!(array_key_exists('user_id', $requestData) && array_key_exists('password', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	
	if ($requestData['user_id'] && $requestData['password']) 
	{
            $matchThese = ['user_id' => $requestData['user_id'], 'password' =>$requestData['password']];
            $user = CM4Promoter::where($matchThese)->limit(1)->get();
            $status = $user->count();
			if ($status == 0) {

                //print_r($data);die;
                $result = collect(["status" => "2", "message" => 'Invalid userid or password', 'errorCode' => '', 'errorDesc' => '', "data" => (object)array()]);
                return $result;
            } else {
				$totalearning=0;
				$id=$user[0]['id'];	
				$type=$user[0]['user_type'];
				if($type=='P')
				{	
				 $matchThese = ['promoter_id' => $user[0]['id']];
				$getdownload = ['promoter_id' => $user[0]['id'], 'is_installed' =>'1'];
				$querytoday="SELECT count(*) as todaycount from cm4_temp_app_user_data where promoter_id=$id and DATE(created_at) =DATE(NOW())";
				
				$querytotalearning="SELECT SUM(amt_earned) AS total FROM `cm4_temp_app_user_data` WHERE promoter_id=$id AND `status`='1'";
				$totalearning= \ DB::select($querytotalearning);
			   $totalearning=isset($totalearning[0]->total)?$totalearning[0]->total:0;
				
				$querytodaydownload="SELECT count(*) as todaydownload from cm4_temp_app_user_data where promoter_id=$id and DATE(install_date) =DATE(NOW())";
				
				}
				else
				{
				$matchThese = ['surviour_id' => $user[0]['id']];	
				$getdownload = ['surviour_id' => $user[0]['id'], 'is_installed' =>'1'];
				$querytoday="SELECT count(*) as todaycount from cm4_temp_app_user_data where surviour_id=$id and DATE(created_at) =DATE(NOW())";
				
				$querytodaydownload="SELECT count(*) as todaydownload from cm4_temp_app_user_data where surviour_id=$id and DATE(install_date) =DATE(NOW())";
				
				}
		 
			$userInfo = CM4TempAppUserData::where($matchThese)->get(['id']);
			$totalcount = $userInfo->count();
			$todaydata= \ DB::select($querytoday);
			$todaycount=$todaydata[0]->todaycount;

			//Total Download
			$totaldownloadquery = CM4TempAppUserData::where($getdownload)->get(['id']);
			$totaldownload = $totaldownloadquery->count();
			//Today Download
			$todaydownload= \ DB::select($querytodaydownload);
			$todaydownloadcount=$todaydownload[0]->todaydownload;
			
			$resultdata=array('id'=>$user[0]['id'],'name'=>$user[0]['name'],'user_id'=>$user[0]['user_id'],'contact_no'=>$user[0]['contact_no'],'user_type'=>$user[0]['user_type'],'referenced_id'=>$user[0]['referenced_id'],'promoter_id'=>$user[0]['refered_by'],'total_count'=>$totalcount,'today_count'=>$todaycount,'total_earning'=>$totalearning,'today_download'=>$todaydownloadcount,'total_download'=>$totaldownload);	
				
				$result = collect(["status" => "1", "message" => 'Bravo! you made it. Login Successful', 'errorCode' => '', 'errorDesc' => '', "data" => $resultdata]);
                return $result;
            }
        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "data" => (object)array()]);
            return $result;
            
        }
    }	
	
	/**
     *This Api to Create new Surveyor .
     *
     * @return Response
     */
    public function addSurviourUser() {
       // \Log::info('Add New Surveyor .');
        $collection = [];
  if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if ($requestData['contact_no'] && $requestData['user_id']) {
            //\DB::enableQueryLog();
            $matchThese = ['user_id' => $requestData['user_id']];
            $match2 = ['contact_no' => $requestData['contact_no']];
            $user = CM4Promoter::Where($matchThese)->orWhere($match2)->take(1)->get();
			$status = $user->count();
			if ($status == 0) {
                $data = [
                    'name' =>$requestData['name'],
                    'user_id' => $requestData['user_id'],
                    'password' => $requestData['password'],
                    'contact_no' => $requestData['contact_no'],
                    'user_type' => $requestData['user_type'],
                    'refered_by' =>isset($requestData['refered_by']) ? $requestData['refered_by'] :'0',
                    ];
                $data = CM4Promoter::create($data);
                $id = ['id' =>$data->id];
                   $reference_id='S'.$data->id;
				   CM4Promoter::where('id',$id)->update(['referenced_id' =>$reference_id]);
				  $user = CM4Promoter::Where($id)->take(1)->get();
               $result = collect(["status" => "1", "message" => 'User successfully register', 'errorCode' => '', 'errorDesc' => '', "data" => $user]);
                return $result;
            } else {
                $result = collect(["status" => "2", "message" => 'User already exit', 'errorCode' => '', 'errorDesc' => '', "data" => $user]);
                return $result;
            }
        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "data" => array()]);
            return $result;
           
        }
    }
	
	/**
     * add people By Surveyors.
     *
     * @return Response
     */
    public function addPeopleBysurveyor()
    {
    $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        // $requestUser = Request::all();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

          $totalRecord =count($requestData["data"]);
         $insertedRecord =0;
        if(count($requestData["data"])==0){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.401'),'errorCode'=>'401','errorDesc'=>\Config::get('constants.results.401'), "device_key" => $token]);
            return $result;
        }
        $flag=0;
      foreach ($requestData["data"] as $value) {
            if (!(array_key_exists('contact_person', $value)
                && array_key_exists('profile_pic', $value)
                && array_key_exists('uploader_id', $value)
                && array_key_exists('profession', $value)
                && array_key_exists('org_name', $value)
                && array_key_exists('age', $value)
                && array_key_exists('gender', $value)
                && array_key_exists('about_me', $value)
                && array_key_exists('contact_no', $value)
                && array_key_exists('start_time', $value)
                && array_key_exists('end_time', $value)
                && array_key_exists('email', $value)
                && array_key_exists('address_source', $value)
                && array_key_exists('location', $value)
                && array_key_exists('locality', $value['location'])
                && array_key_exists('address', $value['location'])
                && array_key_exists('address2', $value['location'])
                && array_key_exists('pincode', $value['location'])
                && array_key_exists('city', $value['location'])
                && array_key_exists('state', $value['location'])
                && array_key_exists('country', $value['location'])
                && array_key_exists('latitude', $value['location'])
                && array_key_exists('longitude', $value['location'])
            )
            ) {
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
                return $result;
            }
			$fields = [
                'contact_person' => $value['contact_person'],
                'uploader_id' => $value['uploader_id'],
                'profession' => $value['profession'],
                'contact_no' => $value['contact_no'],
            ];
            $rules = [
                'contact_person' => 'required',
                'uploader_id' => 'required',
                'profession' => 'required',
                'contact_no' => 'required'
            ];
            $valid = \Validator::make($fields, $rules);
            if ($valid->fails()) {
                return [
                    'status' => '0',
                    'message' => 'validation_failed',
                    'errorCode' => '',
                    'errorDesc' => $valid->errors()
                ];
            }
			   //Surveyors Details
			   $promoter_id=$value['promoter_id'];
			   $surveyorid=$value['surveyor_id'];
			   $surveyor_name=$value['surveyor_name'];
			   $surveyor_contact_no=$value['surveyor_contact_no'];
				//Surveyors Details End
		$matchThese = ['contact_no' => $value['contact_no']];
		$user = CM4UserProfile::where($matchThese)->get();
            $Profilestatus = $user->count();
			//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$value)?$value['others']:"";
			
        $matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4TempAppUserData::where($matchThese)->get();
            $userDatastatus = $user->count();

            if ($Profilestatus == 0 && $userDatastatus == 0) {
                $amt_earned=array_key_exists('amt_earned',$value)?$value['amt_earned']:'0';
				$profession = $value['profession'];
				//new code for lat long
				 if($value["address_source"]==1)
						   {//gps
                    $rec_addressgps=$value["location"]["address"];
						 $latitude = $value["location"]['latitude'];
                  $longtitude = $value["location"]['longitude'];  
						   }
				
				if($value["address_source"]==2)//manual
				{
				   $rec_address=$value["location"]["city"].
                        " ".$value["location"]["state"].
                        " ".$value["location"]["country"].
                        " ".$value["location"]["pincode"];

        $latLng=$this->get_lat_long($rec_address);
			
		$latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
				if(isset($value['profile_pic']) && $value['profile_pic']!="")
				{
				$imagedata= $this->imageUpload($value['profile_pic']);
                }
				else
				{
				$imagedata["name"]="";	
				}
				if($othersprofession=="")
				{
				 
				 $list=$this->multi_implode($value['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				}
				else
				{
				$getcategory=$value['others'];
				 $list=$this->multi_implode($value['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$value['others'];
				}		
			 }
				$category_json=serialize($profession);
				}
                $address=$value['location']['address']."|".$value['location']['address2'];
               
                $data = [
                    "contact_person" => $value["contact_person"],
                    "amt_earned" => $amt_earned,
					"profile_pic" => $imagedata["name"],
                    "uploader_id" => $value["uploader_id"],
                    "profession" =>  $getcategory,
                    "profession_ids" => $category_json,
                    "work_place" => $value["org_name"],
                    "age" => $value["age"],
                    "gender" => $value["gender"],
                    "about_me" => $value["about_me"],
                    "contact_no" => $value["contact_no"],
                    "start_time" => $value["start_time"],
                    "end_time" => $value["end_time"],
                    "email" => $value["email"],
                    "address_source" => $value["address_source"],
                    "locality" =>$value['location']['locality'],
                    "address" => $address,
                    "pincode" => $value['location']['pincode'],
                    "city" => $value['location']['city'],
                    "state" => $value['location']["state"],
                    "country" => $value['location']["country"],
                    "latitude" => $latitude,
                    "longitude" => $longtitude,
					"uploader_name"=>$surveyor_name,
					"uploader_contact"=>$surveyor_contact_no,
					"uploader_name"=>$surveyor_name,
					"promoter_id"=>$promoter_id,
					"surviour_id"=>$surveyorid,
                ];
                 //print_r($data);die;
				 CM4TempAppUserData::create($data);
                $flag=1;
                $insertedRecord++;
			}
         }
            if($flag==1){
				 CM4Promoter::where('id',$surveyorid)->update(['total_uploads' =>$insertedRecord]);
				
                $data =["total_record"=>$totalRecord,"inserted_record"=>$insertedRecord];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'), 'errorCode' => '100', 'errorDesc' => \Config::get('constants.results.100'),"data"=>$data, "device_key" => $token]);

            }else{
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.103'), 'errorCode' => '103', 'errorDesc' => \Config::get('constants.results.103'), "device_key" => $token]);

            }

        return response()->json($result, 200);
    }

    /**
     * Show fetchSurveyorlist.
     *
     * @return Response
     */
    public function fetchSurveyorlist() {
       // \Log::info('Surveyor list fetch.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('promoter_id',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        /* if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        } */
   $is_today=array_key_exists('is_today',$requestData)?$requestData['is_today']:'0';
    $matchThese = ['refered_by' =>$requestData['promoter_id'] ];
        $list=CM4Promoter::where($matchThese)->get(['id as surveyor_id','name','contact_no','shift_time','user_type','referenced_id','refered_by as promoter_id','profile_pic']);
		$xyz=array();
		$totalearning=0;
		foreach($list as $item)
		{
		$surveyor_id=$item->surveyor_id;
		$querytoday="SELECT count(*) as todaycount from cm4_temp_app_user_data where surviour_id=$surveyor_id and DATE(created_at) =DATE(NOW())";
			$todaydata= \ DB::select($querytoday);
			$todaycount=$todaydata[0]->todaycount;
			$item->today_count=$todaycount;
		
		$querytotalcount="SELECT count(*) as totalcount from cm4_temp_app_user_data where surviour_id=$surveyor_id ";
			$totaldata= \ DB::select($querytotalcount);
			$totalcount=$totaldata[0]->totalcount;
			$item->total_count=$totalcount;
		
		
		$querytotalearning="SELECT SUM(amt_earned) AS total FROM `cm4_temp_app_user_data` WHERE surviour_id=$surveyor_id AND `status`='1'";
				$totalearning= \ DB::select($querytotalearning);
			    $totalearning=isset($totalearning[0]->total)?$totalearning[0]->total:0;
		$item->total_earning=$totalearning;
		
		if($todaycount>0 && $is_today==1)
			{
			array_push($xyz,$item);	
			}
		}
		if($is_today==1)
		{
		$newlist=$xyz;	
		}
		else
		{
	   $newlist=$list;
		}
		
		
		$status = $list->count();
    if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>$newlist, "device_key" => $token]);
        } else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }
   return response()->json($data, 200);
    }
	
	/**
     * Show list of Added People By Surveyors.
     *
     * @return Response
     */
    public function SurveyoraddedPeoplelist() {
      //  \Log::info('Survayor added Peoplelist fetch.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('surveyor_id',$requestData)&& array_key_exists('promoter_id',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) !=3) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	$matchThese = ['surviour_id' =>$requestData['surveyor_id']];
		if($requestData['is_today']=='1')
		{
   $list=CM4TempAppUserData::where($matchThese)
            ->whereDate('created_at', '=', \Carbon\Carbon::today()
                ->toDateString())->get(['contact_person','profile_pic','profession','contact_no','address','work_place','status','amt_earned as field_count','is_installed as download_status']);
   $list->each(function ($item) {
            if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
	})->values();
	}
   else
   {
  $list=CM4TempAppUserData::where($matchThese)->get(['contact_person','profile_pic','profession','contact_no','address','work_place','status','amt_earned as field_count','is_installed as download_status']);
  $list->each(function ($item) {

            if($item->profile_pic!='') {
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $item->profile_pic;
            }else{
                $item->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
        })->values();
   }
	$status = $list->count();
        if ($status) {
            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>$list, "device_key" => $token]);
        } 
		else 
		{
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }
   return response()->json($data, 200);
    }
	
	  /**
     * Make Withdraw Request .
     *
     * @return Response
     */
    public function withdrawPromoterRequest() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();

        $created_date = \Carbon\Carbon::today();

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }



        if (!(array_key_exists('promoter_id',$requestData)
            && array_key_exists('amount', $requestData)
		)
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        //$arr= json_decode($requestData['profession']);


        $fields = [
            'promoter_id' => $requestData['promoter_id'],
            'amount' => $requestData['amount'],
        ];
        $rules = [
            'promoter_id' => 'required',
            'amount' => 'required'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }

        $promoter_id =$requestData['promoter_id'];
        $amount =$requestData['amount'];
 $matchThese = ['uid' => $requestData['promoter_id']];
 $user = CM4PiggyBankAccount::where($matchThese)->get();
        $status = $user->count();
        $less=0;
		if($status>0)
	if ($status==0) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.115'), 'errorCode' => '115', 'errorDesc' => \Config::get('constants.results.115'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
		else
		{
        if($user[0]['amt_earned']>$amount)
		{
		$available_amount=$user[0]->amt_earned;
      // return $available_amount;

            $data = [
                "uid" => $promoter_id,
                "request_amt" => $amount,
                "previous_bal" => $available_amount,
                "avail_bal" => $available_amount-$amount,
                "request_date" => $created_date

            ];
            CM4PiggyBankTransaction::create($data);
            CM4PiggyBankAccount::where('uid',$promoter_id)->update(['amt_earned' => $available_amount-$amount]);

            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'), "device_key" => $token]);
  return response()->json($result, 200);
		}
		}	
    $result = collect(["status" => "0", "message" => \Config::get('constants.results.115'), 'errorCode' => '115', 'errorDesc' => \Config::get('constants.results.115'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
	}

		/**
     * Fetch CM4 Do you Know.
     *
     * @return Response
     */
    public function Cm4doyouknow() {
       // \Log::info('Fetch do you know list.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) !=1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
		$isactive=array('is_active'=>1);
		
		$allrecords=CM4Doyouknow::where($isactive)->get(array('id','content','adimage','view_count'));
		$queryformaxdate="SELECT MAX(updated_at) AS maxdate FROM cm4_do_you_know";
				$maxdate= \ DB::select($queryformaxdate);
			    $maxdaterecent=isset($maxdate[0]->maxdate)?$maxdate[0]->maxdate:0;
   
	$status = $allrecords->count();
        if ($status) {
			
			foreach($allrecords as $val)
			{
			$val->adimage = \Config::get('constants.results.root')."/adimage/".$val->adimage;	
			}
			
            $data = collect(["status" => "1",'maxdate'=>$maxdaterecent,"message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'', "data" =>$allrecords, "device_key" => $token]);
        } 
		else 
		{
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
        }
   return response()->json($data, 200);
    }
	
			/**
     * Fetch Cm4 check contact.
     *
     * @return Response
     */
    public function Cm4checkcontact() {
       // \Log::info('Check contact no.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!(array_key_exists('uid',$requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        
		    $uid =$requestData['uid'];
			$surveyor_id =$requestData['surveyor_id'];
			$contact_no =$requestData['contact_no'];
			$promoter_id =$requestData['promoter_id'];
		
		$matchThese = ['contact_no'=>$requestData['contact_no']];

            $user = CM4UserProfile::where($matchThese)->get();
            $Profilestatus = $user->count();
			
			 //$matchThese = ['contact_no' => $value['contact_no']];

            $user = CM4TempAppUserData::where($matchThese)->get();
            $userDatastatus = $user->count();
			
			if($Profilestatus == 0 && $userDatastatus == 0) 
			{	
		$data = collect(["status" => "0","message" =>'No. not exist.','errorCode'=>'','errorDesc'=>'',"device_key" => $token]);
        } 
		else 
		{
                  $data = [
                "uid" => $uid,
                "surveyor_id" => $surveyor_id,
                "contact_no" =>$contact_no,
                "promoter_id" => $promoter_id,
            ];
            CM4ExistingNo::create($data);   			

			$data = collect(["status" => "1","message" =>"No. already exist.",'errorCode'=>'105','errorDesc'=>'','device_key' => $token]);
        }
   return response()->json($data, 200);
    }
	
	/**
     *UploadAudio and Images.
     *
     * @return Response
     */
   
	public function uploadaudio() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
		  
		  if (!(array_key_exists('promoter_id', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('uid', $requestData)&&array_key_exists('surveyor_id', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		
		$image = Request::file('image');
		$audio = Request::file('audio');
		$input['imagename'] = time().'.'.$image->getClientOriginalExtension();
		$input['audioname'] = time().'.'.$audio->getClientOriginalExtension();
		$destinationImage = $_SERVER['DOCUMENT_ROOT']."/uploaded_file/surveyor_pic" ;
		$destinationAudio = $_SERVER['DOCUMENT_ROOT']."/uploaded_file/surveyor_audio" ;
		if (!is_dir($destinationImage))
        {
            mkdir($destinationImage, 0777, true);
        }
		 if (!is_dir($destinationAudio))
        {
            mkdir($destinationAudio, 0777, true);
        }
		 
		$res=$image->move($destinationImage, $input['imagename']);
		$res1=$audio->move($destinationAudio, $input['audioname']);
			
			  $uid =$requestData['uid'];
			$surveyor_id =$requestData['surveyor_id'];
			$contact_no =$requestData['contact_no'];
			$promoter_id =$requestData['promoter_id'];
			
			
			  $data = [
                "uid" => $uid,
                "surveyor_id" => $surveyor_id,
                "contact_no" =>$contact_no,
                "promoter_id" => $promoter_id,
				"image" => $input['imagename'],
				"audio" => $input['audioname'],
            ];
            CM4SurveryorRecording::create($data);   	
			$data = collect(["status" => "1","message" =>"Inserted Successfully.",'errorCode'=>'105','errorDesc'=>'','device_key' => $token]);
           
		return response()->json($data, 200);
		
		
		}
	
		/**
     * Cm4UpdateSurveyorsLoc.
     *
     * @return Response
     */
    public function Cm4UpdateSurveyorsLoc() {
        //\Log::info('Update Surveyor Locations.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

       if (!(array_key_exists('promoter_id', $requestData)
            && array_key_exists('surveyor_id', $requestData)
            && array_key_exists('current_address', $requestData)&&array_key_exists('current_latitude', $requestData)&&array_key_exists('current_longitude', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		 $data = [
                "surveyor_id" => $requestData['surveyor_id'],
                "promoter_id" => $requestData['promoter_id'],
                "current_address" =>$requestData['current_address'],
                "current_latitude" => $requestData['current_latitude'],
				"current_longitude" => $requestData['current_longitude'],
				"working_time" => $requestData['working_time'],
				
            ];
            $insertrec=CM4SurveryorLocation::create($data);  
			if($insertrec)
			{
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
			else
			{
			
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
    }
	
	
		/**
     * Cm4CallsEarning.
     *
     * @return Response
     */
    public function Cm4CallsEarning() {
        //\Log::info('Insert Calls Earning.');
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
  $created_date = \Carbon\Carbon::today();
  if (!(array_key_exists('uid', $requestData)
            && array_key_exists('contact_no', $requestData)
            && array_key_exists('call_date', $requestData)&&array_key_exists('call_time', $requestData)&&array_key_exists('call_duration', $requestData)&&array_key_exists('banner1_duration', $requestData)&&array_key_exists('banner1_earning', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
		
		$entrydate=$requestData['call_date'];
		$todaydate=date('Y-m-d');
		 //$todaydate='2016-12-18';
		$uid=$requestData['uid'];
		$requested_contact=$requestData['my_contact'];
		$allow=0;
		
		$getmycontact="SELECT contact_no from cm4_user_profile where id='".$uid."'";
		$getcontactdetails= \ DB::select($getmycontact);
		$getcontacts=$getcontactdetails[0]->contact_no;
		
		if($getcontacts==$requested_contact)
		{
		$allow=1;	
		}
		
		
		$entrylastcount="SELECT call_date,group_id,ad_id from cm4_ad_data order by id desc limit 1";
		$getgroup= \ DB::select($entrylastcount);
		$lastcalldate=$getgroup[0]->call_date;
		$lastgroupid=$getgroup[0]->group_id;
		$ad_id=$requestData['ad_id'];
		

		$adresponse=array_key_exists('ad_response',$requestData)?$requestData['ad_response']:0;
		if($lastgroupid==0)
		{
		$updategroupid=1;	
		}
		if(($lastcalldate==$todaydate) && $lastgroupid!=0)
		{
		$updategroupid=$lastgroupid;	
		}
		else
		{
			$updategroupid=$lastgroupid+1;
		}
		
		$mycontact=$requestData['my_contact'];
		$calldate=$requestData['call_date'];
		$calltime=$requestData['call_time'];
		//$banner1_earning=$requestData['banner1_earning'];
		//$banner2_earning=$requestData['banner2_earning'];
		$banner1_earning='0.00';
		$banner2_earning='0.00';
		$callduration=$requestData['call_duration'];
		if($callduration>10 && $banner1_earning<0.11 && $banner2_earning<0.11 && $allow==1 && $requestData['contact_no']!="")
		{
			
		$data = [
                "uid" => $requestData['uid'],
                "user_id" =>$requestData['user_id'],
				"my_name" =>$requestData['my_name'],
				"my_contact" =>$requestData['my_contact'],
				"my_address" =>$requestData['my_address'],
				"call_type" => $requestData['call_type'],
                "contact_no" =>$requestData['contact_no'],
                "call_date" => $requestData['call_date'],
				"call_time" => $requestData['call_time'],
				"call_duration" => $requestData['call_duration'],
				"banner1_duration" => $requestData['banner1_duration'],
				"banner2_duration" => $requestData['banner2_duration'],
				"contact_person" => $requestData['contact_person'],
				"banner1_earning" => $banner1_earning,
				"banner2_earning" => $banner2_earning,
				"group_id"=>$updategroupid,
				"ad_response"=>$adresponse,
				"ad_id"=>$ad_id
            ];
            
		$entrycount="SELECT count(*) as entrycount from cm4_ad_data where my_contact='".$mycontact."' and call_date='".$calldate."' and call_time='".$calltime."'";
		$countquery= \ DB::select($entrycount);
		if($countquery[0]->entrycount==0)
		{
			$insertrec=CM4AdRecord::create($data);  
			//PIGGY BANK AC 
			//working
			$uid =$requestData['uid'];
			//$amount =(float)$requestData['banner1_earning']+(float)$requestData['banner2_earning'];
			$amount='0.00';
			$matchThese = ['uid' => $uid];
			$user = CM4PiggyBankAccount::where($matchThese)->get(['uid']);
        $status = $user->count();
        if($status==0)
		{
		         $data = [
                     "user_name" => $requestData['my_name'],
                     "contact_no" => $requestData['my_contact'],
                     "address" => $requestData['my_address'],
                     "uid" => $uid,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
                     "amt_earned" => $amount,
                     ];
            CM4PiggyBankAccount::create($data);	
		
            $data = [
                "uid" => $uid,
                "request_amt" =>0,
                "previous_bal" =>0,
                "avail_bal" =>$amount,
                "request_date" => $created_date
			];
            CM4PiggyBankTransaction::create($data);
	  \ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $amount where id=$uid");
	  
	  //update to cc_card
		\DB::connection('a2billing')->statement("update cc_card set credit=credit + $amount where phone='".$mycontact."'");
	  }
		else
		{
		\ DB::statement("UPDATE cm4_user_profile SET piggy_bal = piggy_bal + $amount where id=$uid");
			\ DB::statement("update piggy_bank_ac set amt_earned=amt_earned + $amount where uid=$uid");
			
			//update to cc_card
			\DB::connection('a2billing')->statement("update cc_card set credit=credit + $amount where phone='".$mycontact."'");
			//\ DB::statement("update piggy_bank_transaction set avail_bal=avail_bal + $amount where uid=$uid");
		}	
		if($insertrec)
			{
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"insertid"=>$insertrec->id,"data" =>array(), "device_key" => $token]);
			}
		}
		}
		else
			{
			 $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);	
			}
   return response()->json($data, 200);
    }
	
	/**
     * CM4HOMEFFEDS SCREEN.
     *
     * @return Response
     */
    public function Cm4Homefeeds() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       $created_date = date('Y-m-d : H:i:s');
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
		} else {
            $requestData = Request::all();
        }
	    if (!(array_key_exists('uid', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $userId=$requestData['uid'];
    
	//Version Code
		$userappversion="";
		$latestversion="116";
		if(array_key_exists('version_code',$requestData))
		{
		$userappversion=$requestData['version_code'];
		$userid=$requestData['uid'];
		$matchThese = ['user_id' => $requestData['uid']];

        $appinfo = CM4UserVersion::where($matchThese)->get();
        $statuscount = $appinfo->count();
		if($statuscount==0)
		{
		  $appvesion =[
                'user_id' => $requestData['uid'],
                'user_app_version' => $userappversion,
					 ];	
			CM4UserVersion::create($appvesion);
		}
		else
		{
		CM4UserVersion::where('user_id',$userid)->update(['user_app_version' =>$userappversion]);	
		}
		
		}
	
	
	
	$today_date =date('Y-m-d');
	//FOR Consultants	
	$selectqry=\ DB::select("select id ,contact_person,per_min_val,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service,video_id,video_title,online_status,Is_youtube,is_verified from cm4_premium_customer where video_id!='' order by RAND()");
	
	if(isset($selectqry[0])>0)
		{
		foreach($selectqry as $value)
		{
		if($value->category_ids!="")
		{
		$tags=$value->category_ids;
		$selecttags=\ DB::select("SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($tags) and `cm4_categories`.`type_id`=1");	
	//	echo $selecttags[0]->tags;die;
		$value->tags=$selecttags[0]->tags;
		}
		else
		{
			$value->tags="";
		}		
	 if($value->call_time!="") 
	 {
            $time= $this->today_timing($value->call_time);
                 $time=str_replace("-","|",$time);
				$value->today_timing=$time;
       }
		else
		{
			$value->today_timing="";
		}	
		
		if($value->contact_person!=" " || $value->contact_person!="")
			{
			$value->user_name=$value->contact_person;	
			}
			if(isset($value->contact_person)) 
			{
				$value->contact_person = $value->contact_person;
            }
			else
			{
				$value->contact_person="";
			}
             $matchThese=['uid'=> $userId,'favid'=>$value->id,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
           $value->favourite_status=  $user->count()>0?1:0;
          

            if($value->profile_pic!='') {
                $value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
            }else{
                $value->profile_pic = "" ;
            }
		
		 if($value->video_id!='') {
				$value->thumbnail_big="https://i.ytimg.com/vi/$value->video_id/sddefault.jpg";
			   $value->video_id="https://www.youtube.com/watch?v=$value->video_id";
				
            }else{
                 $value->video_id="";
				$value->thumbnail_big="";
            }
		  $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$value->id."'");	
		    if(count($selectofferrate)>0)
			{
			$value->offer_rate=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$value->offer_rate='';		
			}		
			$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$value->id."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$value->reviewcount=$raterevqryex[0]->reviewcount;	
			$value->avgrating=$raterevqryex[0]->avgrating;
			}
			else
			{
			if(count($raterevqryex)>0)
			{
			$value->reviewcount='0';	
			$value->avgrating='0';
			}	
			}
		
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$value->id."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$value->force_close='1';	
			}
			else
			{
			$value->force_close='0';	
			}
	   }
	 } 
     if(count($selectqry)>0)
	  {
		  $result = collect(["status" => "1", "message" => 'Home Feeds Data', 'errorCode' => '', 'errorDesc' => '', "data" => $selectqry]);
                return $result;
            }
         else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "data" => (object)array()]);
           return $result;
	  //return response()->json($finalresult, 200);
		}
	}
		

/**
     * CM4HOMEFFEDS SCREEN.
     *
     * @return Response
     */
    public function Cm4Homefeeds_ios() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
       $created_date = date('Y-m-d : H:i:s');
 if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
		} else {
            $requestData = Request::all();
        }
	    if (!(array_key_exists('uid', $requestData)
            )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        $userId=$requestData['uid'];
    $today_date =date('Y-m-d');
	//FOR Consultants	
	$selectqry=\ DB::select("select id ,contact_person,per_min_val,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,call_time,locality,category as service,video_id,video_title,online_status,Is_youtube from cm4_premium_customer where video_id!='' order by RAND()");
	
	if(isset($selectqry[0])>0)
		{
		foreach($selectqry as $value)
		{
		if($value->category_ids!="")
		{
		$tags=$value->category_ids;
		$selecttags=\ DB::select("SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($tags) and `cm4_categories`.`type_id`=1");	
	//	echo $selecttags[0]->tags;die;
		$value->tags=$selecttags[0]->tags;
		}
		else
		{
			$value->tags="";
		}		
	 if($value->call_time!="") 
	 {
            $time= $this->today_timing($value->call_time);
                 $time=str_replace("-","|",$time);
				$value->today_timing=$time;
       }
		else
		{
			$value->today_timing="";
		}	
		
		if($value->contact_person!=" " || $value->contact_person!="")
			{
			$value->user_name=$value->contact_person;	
			}
			if(isset($value->contact_person)) 
			{
				$value->contact_person = $value->contact_person;
            }
			else
			{
				$value->contact_person="";
			}
             $matchThese=['uid'=> $userId,'favid'=>$value->id,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
           $value->favourite_status=  $user->count()>0?1:0;
          

            if($value->profile_pic!='') {
                $value->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
            }else{
                $value->profile_pic = "" ;
            }
		
		 if($value->video_id!='') {
				$value->thumbnail_big="https://i.ytimg.com/vi/$value->video_id/sddefault.jpg";
			   $value->video_id="https://www.youtube.com/watch?v=$value->video_id";
				
            }else{
                 $value->video_id="";
				$value->thumbnail_big="";
            }
		  $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$value->id."'");	
		    if(count($selectofferrate)>0)
			{
			$value->offer_rate=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$value->offer_rate='';		
			}		
			$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$value->id."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$value->reviewcount=$raterevqryex[0]->reviewcount;	
			$value->avgrating=$raterevqryex[0]->avgrating;
			}
			else
			{
			if(count($raterevqryex)>0)
			{
			$value->reviewcount='0';	
			$value->avgrating='0';
			}	
			}
		
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$value->id."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$value->force_close='1';	
			}
			else
			{
			$value->force_close='0';	
			}
	   }
	 } 
     if(count($selectqry)>0)
	  {
		  $result = collect(["status" => "1", "message" => 'Home Feeds Data', 'errorCode' => '', 'errorDesc' => '', "data" => $selectqry]);
                return $result;
            }
         else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "data" => (object)array()]);
           return $result;
	  //return response()->json($finalresult, 200);
		}
	}
	

		/**
     * Show list of getVloggers.
     *
     * @return Response
     */
    public function getVloggers() {
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	if(!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
	
	$uid=$requestData['uid'];

	$qry="SELECT id,user_name,contact_person,user_rating,contact_person,contact_no,category,profile_pic FROM `cm4_user_profile` where id in ('982028',
'981564',
'982924',
'986587',
'984056',
'983799',
'982033',
'986350',
'983621',
'982594',
'983370',
'986362',
'983251',
'983052',
'983103',
'983191',
'983901',
'983361',
'982590',
'983054',
'983082',
'983329',
'982384',
'986538',
'982584',
'984295',
'983363',
'984075',
'981281',
'983003',
'983794',
'986535',
'980790',
'986402',
'982666')";
			$bloggerInfo= \DB::select($qry);
	if(count($bloggerInfo)>0)
	{
	foreach($bloggerInfo as $blog)
	{
		if($blog->contact_person=="" || $blog->contact_person==" ")
		{
		$blog->contact_person=$blog->user_name;
		}
	
	if($blog->profile_pic!="" || $blog->profile_pic!=" ")
		{
		$blog->profile_pic=\Config::get('constants.results.root')."/user_pic/" .$blog->profile_pic;
		}
	}
	
	$data = collect([ "status" => "1","message" =>"Vlogger Details.",'errorCode'=>'200','errorDesc'=>"","userlist"=>$bloggerInfo,"device_key" => $token]);
	}
		else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"userlist"=>array(),"device_key" => $token]);
        }
	return response()->json($data, 200);
		}
	
	 
    public function getBloggers() {
		
        $collection=[];
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
	if(!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 1) {
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
        $fields = [
            'uid' => $requestData['uid']
        ];
        $rules = [
            'uid' => 'numeric'
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'message' => 'validation_failed',
                'errors' => $valid->errors()
            ];
        }
	
	$uid=$requestData['uid'];

	$qry="SELECT uid,user_name,contact_person,user_rating,contact_person,contact_no,category,per_min_val,profile_pic FROM `cm4_bloggers`";
			$bloggerInfo= \DB::select($qry);
	if(count($bloggerInfo)>0)
	{
	foreach($bloggerInfo as $blog)
	{
		if($blog->contact_person=="" || $blog->contact_person==" ")
		{
		$blog->contact_person=$blog->user_name;
		}
	
	if($blog->profile_pic!="" || $blog->profile_pic!=" ")
		{
		$blog->profile_pic=\Config::get('constants.results.root')."/user_pic/" .$blog->profile_pic;
		}
	}
	
	$data = collect([ "status" => "1","message" =>"Blogger Details.",'errorCode'=>'200','errorDesc'=>"","userlist"=>$bloggerInfo,"device_key" => $token]);
	}
		else {
            $data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"userlist"=>array(),"device_key" => $token]);
        }
	return response()->json($data, 200);
		}
	
	/******************************************PROMOTER API END******************************************************************/
	//Get Call Details of Telecallers ..
	public function getusecalltime(){
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        $created_date = \Carbon\Carbon::today();
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        }else{
            $requestData = Request::all();
        }
        //print_r($milliseconds = round(microtime(true) * 1000));
    
        if(!(array_key_exists('uesr_id', $requestData) && array_key_exists('time',$requestData) && array_key_exists('callstatus',$requestData))){
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 3) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
        }
        $seconds = $requestData['time'] / 1000;
        $date_cur=date("Y-m-d", $seconds);
        $time_cur=date("H:i:s", $seconds); //print_r($date_cur); print_r($time_cur); exit();
        if($requestData['callstatus']==1){
        	$data = [
                "user_id" => $requestData['uesr_id'],
                "date" => $date_cur,
                "start_time" =>$time_cur,
                "end_time" =>'',
                "created_at"=>date("Y-m-d H:i:s"),
                "updated_at"=>''
		    ];
            $insertrec=CM4UserCalltime::create($data);  
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"device_key" => $token]);
        }else{
        	$qry="SELECT id FROM `cm4_user_calltime` WHERE`cm4_user_calltime`.`user_id`=".$requestData['uesr_id']." order by id desc limit 1";
			$gettags= \DB::select($qry);
			if(!empty($gettags)){
				$user_pro = CM4UserCalltime::find($gettags[0]->id);
				$user_pro->end_time = $time_cur;
				$user_pro->ms=$requestData['time'];
				$user_pro->updated_at = date("Y-m-d H:i:s");
				$user_pro->save();
				$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"device_key" => $token]);
			}else{
				$result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
			}
			
        }
        return response()->json($result, 200);
       
	}

	public function searchnewapi() {
 	$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
        } else {

            $requestData = Request::all();
        }

        if (!(array_key_exists('text', $requestData)
            &&array_key_exists('uid', $requestData)
            &&array_key_exists('start', $requestData)
            &&array_key_exists('rows', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }

        if (count($requestData) < 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            return $result;
        }
	
        $fields = [
            'text' => $requestData['text'],
            'uid' => $requestData['uid']
        ];
        $rules = [
            'text' => 'required',
             'uid' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
        }
     
	 $text = preg_replace('!\s+!', '+', $requestData['text']);
        $userId=$requestData['uid'];
        $start=$requestData['start'];
        $rows=$requestData['rows'];
       
  $records=[];
   $tags =[];
  $newarray=array();
  $blogger_ids="";
     
 if($requestData['filter']!=""){
    $filter= $requestData['filter'];
      $filter_all=' AND -tags:"'.urlencode($filter).'"';
	$filter_arr= explode(',',$filter);
    // return $filter_arr;
     $filter_str="";
     foreach($filter_arr as $value)
         $filter_str.=' AND -tags:"'.$value.'"';
		$filterval=urlencode($filter_str);
	
     $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,dist:geodist(geolocation,$lat,$long),tags:tags
      &defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&fq=({!geofilt pt=$pt
      sfield=geolocation d=$distance}$filterval$filter_all)&sort=geodist(geolocation,$lat,$long)+asc";
 }else{
     $premium_response["response"]["numFound"]=0;
   $findme   = '@cm4';
   $pos = strpos($text,$findme);  
	
	//For Youtubers
	$popular_youtube_ids="";
	$popular_youtube_ids_or="";
	//For SSC Exam Preparation
	$ssc_ids="";
	$board_ids="";
	if($text=='Popular+Youtubers' or $text=='SSC+Exam+Preparation' or $text=='Poet' or $text=='Board+Exam+Preparation' or $text=='Astrology' 
		or $text=='Relationship')
	{
		if($text=='Relationship'){
			$matchThese = ['category' => 'Relationship Consultants'];
		}else{
			$matchThese = ['category' => $requestData['text']];
		}
		
		$user = CM4CategoryUser::where($matchThese)->orderBy('order_by', 'ASC')->get(['user_id']);
		foreach ($user as $key => $value) { 
		$userid_new[]=$value->user_id;
		}
		$ids=$userid_new;
		$total_count=count($ids)-1;	
		$count=0;
		foreach($ids as $ker => $id){
			if($total_count==$ker){
				$popular_youtube_ids_or.=$ker;
			}else{
				$popular_youtube_ids_or.="if(termfreq(id,'".$id."'),".$ker.",";
				$close_bracket[]=")";
			} 


			if($count==0){
				$popular_youtube_ids.="id:$id";
		    }else{
				$popular_youtube_ids.=" OR id:$id";
			}

			$count ++;
		}
		$close_bracket_im=implode("",$close_bracket);
		$popular_youtube_ids_or.=$close_bracket_im;
		$popular_id="";
		if($popular_youtube_ids!=""){
			$popular_id="($popular_youtube_ids)";
		}	
		$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$popular_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=$popular_youtube_ids_or asc";
	}
	

	
	else
	{

	if($text=='Call+girls'){
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
		return response()->json($result, 200);
	}
	if($text=='Call+girl'){
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
		return response()->json($result, 200);
	}
	if($text=='Nightlife'){
		$result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
		return response()->json($result, 200);
	}
	$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	
	}
	
	
	  $premium_url = preg_replace('!\s+!', '+', $premium_url);
	
	  //return $details_url;
       $premium_response    = file_get_contents($premium_url);
       $premium_response = json_decode($premium_response,true);
	 $premium_response_arr=  $premium_response["response"]["docs"];
	 if(count($premium_response_arr)>0)
	{	
	$count=0;
	foreach($premium_response_arr as $val){
		 
			if($count==0)
			{
			$blogger_ids.="-id:$val[id]";	
			}	
			else
			{
		$blogger_ids.=" AND -id:$val[id]";
			}
		 $filterids=urlencode($blogger_ids);
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="" ) {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }
			 else if($val['service']!="")
			{
			if (preg_match("/;/",$val['service'])) {
			
			$category=explode(';',$val['service']);
			foreach($category as $getcategory)
			{
			$newsearchtext=explode(':',$getcategory);
			$searchtext[]=$newsearchtext[0];
			}
			} else {
		$category=explode(':',$val['service']);
		$searchtext[]=$category[0];
		}
	   
	    $categorytext=implode(",",$searchtext);
		unset($searchtext);
		//$categorytext=$searchtext[0];
			 $val['tags']=$categorytext;
			} 
			
			else {
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		/* //Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		if($searched_uid=='999545'){
			$val['force_close']='1';	
		}else{
			$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0){
				$val['force_close']='1';	
			}else{
				$val['force_close']='0';	
			}
		}
		
		$val['is_premium']='1';	
		
			//Get Total Call call count received.
		
		$select_video="SELECT video_id,video_title,per_min_val,online_status,Is_youtube,is_verified from cm4_premium_customer where id='".$searched_uid."'";
		$getvideo= \ DB::select($select_video);
		if(count($getvideo)>0)
		{
			if($getvideo[0]->video_id!='') {
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
            }else{
                
				 $val['thumbnail_big']="";
				 $val['video_id']="";
            }
		$val['per_min_val']=$getvideo[0]->per_min_val;
		$val['online_status']=$getvideo[0]->online_status;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		$val['video_title']=$getvideo[0]->video_title;
		$val['is_verified']=$getvideo[0]->is_verified;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";
		$val['is_youtube']="0";	
		$val['video_title']="";
		$val['is_verified']=0;
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		//Select Profile Pic
		$select_profile_pic="SELECT profile_pic from cm4_user_profile where id='".$searched_uid."' and is_installed='1'";
		
		$getimage= \ DB::select($select_profile_pic);
		if(count($getimage)>0)
		{
			if($getimage[0]->profile_pic!='') {
				$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $getimage[0]->profile_pic;
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }
		}
		else
		{
		$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;	
		}
		//END Select Profile Pic
		
			/* if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            } */

            array_push($records,$val);
            array_push($tags, $val['tags']);
		$count ++;
	 }
	}
	$searchuser_id="";
	if($blogger_ids!="")
    {
	$searchuser_id=	"&fq=($blogger_ids)";
	}	
	$distance=500;	
	 $details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)$searchuser_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	 
	
	 }
 
//echo $details_url;die;
 $details_url = preg_replace('!\s+!', '+', $details_url);
       //return $details_url;
        $response    = file_get_contents($details_url);
       $response = json_decode($response, true);
      
	   // $coll_res =(object)$response;
    $response["responseHeader"]["params"]["fq"]="CallMe4";
	$response_arr= $response["response"]["docs"];
    foreach($response_arr as $val){
		  
            $val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            
			if(!(array_key_exists('live_status', $val)))
			{
			$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" ")
			{
			$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }
			else
			{
				$val['contact_person']="";
			}
            $val['latitude']=$val['latitude'];
            $val['longitude']=$val['longitude'];
            $val['service']=$val['service'];

            $val['address']=$val['address'];
            $val['call_time']=$val['call_time'];
            $val['locality']=$val['locality'];
            if($val['call_time']!="") {
                 $time= $this->today_timing($val['call_time']);
				 $time=str_replace("-","|",$time);
                 $val['today_timing']=$time;
            }else{
                $val['today_timing']="";
            }
            if(  array_key_exists("tags",$val)) {
                $val['tags'] = $val['tags'];
            }else{
                $val['tags']="";
            }
           
			$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $val['favourite_status']=  $user->count()>0?1:0;
           
		  
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$val['id'];
		$searched_contact=$val['contact_no'];	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$val['reviewcount']=$raterevqryex[0]->reviewcount;	
			$val['avgrating']=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		/* $callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$val['callcount']=$callcount;
		} */
		
		
		$today_date=date('Y-m-d');
		//Get Force Rate Update
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$val['force_close']='1';	
			}
			else
			{
			$val['force_close']='0';	
			}
		
		//GET PERMINUTE VALUE AND ONLINE STATUS
		$querystatus="SELECT per_min_val,is_callback as online_status from cm4_user_profile where id='".$searched_uid."'";
		$status_query_ex= \ DB::select($querystatus);
		
		if(count($status_query_ex)>0)
		{
		$val['per_min_val']=$status_query_ex[0]->per_min_val;
		$val['online_status']=$status_query_ex[0]->online_status;
		
		}
		else
		{
		$val['per_min_val']="0";
		$val['online_status']="1";	
		}	
		$val['is_premium']='0';	
		
			//Get Total Call call count received.
		$val['is_youtube']="0";
		$select_video="SELECT video_id,video_title,Is_youtube,is_verified from cm4_premium_customer where id='".$searched_uid."'";
		
		$getvideo= \ DB::select($select_video);
		
		if(count($getvideo)>0)
		{
			if(isset($getvideo[0]->video_id) && $getvideo[0]->video_id!='') {
				$video_id=$getvideo[0]->video_id;
				$val['thumbnail_big']="https://i.ytimg.com/vi/".$getvideo[0]->video_id."/sddefault.jpg";
			    $val['video_id']="https://www.youtube.com/watch?v=".$getvideo[0]->video_id;
				$val['video_title']=$getvideo[0]->video_title;
			
            }else{
                 $val['thumbnail_big']="";
				 $val['video_id']="";
				 $val['video_title']="";
            }
		//print_r($val);die;
		$val['is_youtube']=$getvideo[0]->Is_youtube;
		$val['is_verified']=$getvideo[0]->is_verified;
		}
		else
		{
		$val['thumbnail_big']="";
		$val['video_id']="";	
		$val['video_title']="";
		$val['is_verified']=0;
		}
		
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0)
			{
			$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$val['offer_rate']='';		
			}
		
		
		if($val['profile_pic']!='') {
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $val['profile_pic'];
            }else{
                $val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
            }

            array_push($records,$val);
            array_push($tags, $val['tags']);
        }
		
	   $list = implode(',',array_unique($tags));
        $newlist=explode(',',$list);
		$newtags=implode(',',array_unique($newlist));
		$newtags=trim($newtags,",");	
		$response["response"]["tags"]=$newtags;
        $response["response"]["docs"]=$records;
		
     $total_record=$response["response"]["numFound"] + $premium_response["response"]["numFound"];
	  $response["response"]["numFound"]=$total_record;
	 //   return $response;
	 $data = array("text" => $text,
                "uid" => $userId,
                "record_count"=>$response["response"]["numFound"],
			);
		CM4Search::create($data); 
           
        if ($total_record!=0) {
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response, "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }

    	public function verifySms_new() { //print_r('F');exit();
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
		
		
		} else {

            $requestData = Request::all();
        } 
	
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('code', $requestData)
            && array_key_exists('device_id', $requestData)
            && array_key_exists('country_code', $requestData)
        )) {
            //$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 4) {
            // $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')], "device_key" => $token]);
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

        $fields = [
            'phone' => $requestData['phone'],
            'code' => $requestData['code'],
            'device_id' => $requestData['device_id'],
            'country_code' => $requestData['country_code']
        ];
        $rules = [
            'phone' => 'required',
            'code' => 'required|numeric|digits:4',
            'device_id' => 'required',
            'country_code' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            
        }
		
		 //Check User Details Status
		 $mobileno='91'.$requestData['phone'];
		$matchThese = ['contact_no' => $requestData['phone']];
		$userdetails = CM4UsersDetails::where($matchThese)->get(['id']);
        $userdetailsstatus = $userdetails->count();
		
        $matchThese = ['phone' => $mobileno,'code'=>$requestData['code']];

        $user = CM4UserInfo::where($matchThese)->get(['id']);
        $status = $user->count(); 
//print_r($status);exit();
        //-------sim detail-----------------------------------//
            
      
		if ($status != 0) {

         $user = CM4UserInfo::find($user[0]['id']);
     
			$user->status = 1;
            $user->save();

            $matchThese = ['contact_no' => $user->phone];
            $user = CM4UserProfile::where($matchThese)->get();


            $matchThese1 = ['contact_no' => $requestData['phone']];
            $user_sim = CM4UserSimno::where($matchThese1)->get();
            if(count($user_sim)!=0){
				$sim_number=$user_sim[0]->sim_number;
			}else{
				$sim_number='';
			}

            
         
		$status = $user->count();
			
		 $phone='91'.$requestData['phone'];
            $email="";
            $name="";
            if($status==0){
				$referal=$this->gen_referal_code();
                $genData= $this->register($phone,$email,$name);
				//return $genData;	
                $userId=$genData['user_id'];
                $fdial=$genData['cc_fdial'];
                $pass=$genData['cc_password'];
				$piggybal="5.00";   
	$category_json="a:0:{}";
	$userinfo=array('user_id'=>$userId,'c_code'=>$requestData['country_code'],'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$matchThese['contact_no'],'email'=>'','about_us'=>'','city'=>'','state'=>'','locality'=>'','address'=>'','category'=>'','category_ids'=>0,'category_json'=>$category_json,'data_source'=>'','profile_pic'=>'','call_time'=>'','latitude'=>'','longitude'=>'','cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'1','created_at'=>date('Y-m-d H:i:s'),'pincode'=>'','referal_code'=>'','piggy_bal'=>$piggybal,'is_installed'=>'1');
		$data=CM4UserProfile::create($userinfo);
			  
			  //Create User Piggy Ac
		 $piggybankdata = [
                     "user_name" => '',
                     "contact_no" => $matchThese['contact_no'],
                     "address" => '',
                     "uid" =>$data->id,
                     "bank_name" =>'',
                     "bank_ifsc_code" =>'',
                     "account_number" => '',
					 "amt_earned"=>'5.00'
                     ];
            CM4PiggyBankAccount::create($piggybankdata);	
		
		//Check users in cm4_user_refers Table
		$check_user=['uid' =>$data->id];	
		  $Refer = CM4UserRefer::where($check_user)->get(['id']);
			if(count($Refer)==0)
			{
		$cm4_user_refers=array('uid'=>$data->id,'refer_code'=>$referal,'earned_by_uid'=>'','created_at'=>date('Y-m-d H:i:s'));	  
			$datarefer=CM4UserRefer::create($cm4_user_refers);
			}
			  
			
                $finalData=['user_registration_status'=>'0','userdetailsstatus'=>$userdetailsstatus,'user'=>[
                    'id'=>$data->id,
                    'user_id'=>$userId,
                    'name'=>'',
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/noImage.png",
                    'gender'=>'',
                    'locality'=>'',
                    'age'=>'',
                    'address'=>'',
                    'country'=>'',
                    'city'=>'',
                    'state'=>'',
                    'latitude'=>'',
                    'longitude'=>'',
                    'call_time'=>'Monday|10:00 AM - 6:00 PM,Tuesday|10:00 AM - 6:00 PM,Wednesday|10:00 AM - 6:00 PM,Thursday|10:00 AM - 6:00 PM,Friday|10:00 AM - 6:00 PM,Satuday|10:00 AM - 6:00 PM,Sunday|10:00 AM - 6:00 PM',
                    'about_us'=>'',
                    'profile_status'=>'',
                    'user_rating'=>'',
                    'marital_status'=>'',
                    'contact_person'=>'',
                    'contact_no'=>$matchThese['contact_no'],
                    'verfication_code'=>'',
                    'verfication_status'=>'',
                    'device_id'=>$requestData['device_id'],
                    'cc_password'=>$pass,
                    'email'=>'',
                    'cc_fdail'=>$fdial,
                    'category'=>'',
                    'piggy_bal'=>0,
                    'live_status'=>1,
                    'referal_code'=>$referal,
                    'update_profile_status'=>0,
                    'data_source'=>'',
					'address2'=>'',
					'per_min_val'=>'5.00',
					'is_logged_from_another_device'=>'0',
					'user_searchid'=>'',
					'youtube_link'=>'',
					'facebook_link'=>'',
					'twitter_link'=>'',
					'instagram_link'=>'',
					'snapchat_link'=>'',
					'blog_link'=>'',
					'msg_bf_call'=>'',
					'more_about'=>'',
					'reviewcount'=>0,
					'avgrating'=>0,
					'callcount'=>0,
					'simno'=>$sim_number,
					'other_contact_no'=>''
					
               ]];
               
			   $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
              
           }else{

                //update live status	
			 $contact_no=$requestData['phone'];
			
			CM4UserProfile::where('contact_no',$contact_no)->update(['live_status' =>1,'is_installed'=>1]);
				
				//Check Social Data
				$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0)
				{
				   $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}	
				else
				{
				    $youtube_link='';
					$facebook_link='';
					$twitter_link='';
					$instagram_link='';
					$snapchat_link='';
					$blog_link='';
					$msg_bf_call='';
					$more_about='';		
				}		
				
				
		     //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		 $searched_uid=$user[0]->id;
		 $searched_contact=$user[0]->contact_no;	
		  $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
		  $raterevqryex= \ DB::select($rate_rev_qry);
			$reviewcount=0;
			$avgrating=0;
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
				
				
				if($requestData['device_id']==$user[0]->device_id){

                  
                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];

                   // $user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";

                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                    $user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					$user[0]['simno']=$sim_number;
					$user[0]['other_contact_no']=$user[0]->marital_status;
					
					
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                 

                }else{

                    $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device'] = "1";

                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];
                    //$user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";
                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    $user[0]['service']=$user[0]['category'];
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
						else
						{
						$user[0]['service_ids']=array();	
						}
					$user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					$user[0]['simno']=$sim_number;
					$user[0]['other_contact_no']=$user[0]->marital_status;
					
					
                    unset($user[0]['category_ids']);
                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?0:1,'userdetailsstatus'=>$userdetailsstatus,'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                    
                }
            }

        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
          
        }

        return response()->json($result, 200);
    }


    public function updateUserProfile_new() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        //$user = Request::all();
		$created_date = date('Y-m-d : H:i:s');

        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();

        } else {
            $requestData = Request::all();
        }

		if (!(array_key_exists('contact_person', $requestData)
            && array_key_exists('profile_pic', $requestData)
            && array_key_exists('id', $requestData)
            && array_key_exists('profession', $requestData)
            && array_key_exists('contact_no', $requestData)
             && array_key_exists('address_source', $requestData)
            && array_key_exists('location', $requestData)
            && array_key_exists('locality', $requestData['location'])
            && array_key_exists('address', $requestData['location'])
            && array_key_exists('address2', $requestData['location'])
            && array_key_exists('pincode', $requestData['location'])
            && array_key_exists('city', $requestData['location'])
            && array_key_exists('state', $requestData['location'])
            && array_key_exists('country', $requestData['location'])
            
        )
        ) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }

       $fields = [
            'id' => $requestData['id']
        ];
        $rules = [
            'id' => 'required',
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status' => '0',
                'message' => 'validation_failed',
                'errorCode' => '',
                'errorDesc' => $valid->errors()
            ];
        }
       $data=   $this->imageUpload($requestData['profile_pic']);
			 if($requestData["address_source"]==1)
						   {//gps
                    $rec_addressgps=$requestData["location"]["address"];
						 $latitude = $requestData["location"]['latitude'];
                  $longtitude = $requestData["location"]['longitude'];  
						   }
				
				if($requestData["address_source"]==2)//manual
				{
				   $rec_address=$requestData["location"]["city"].
                        " ".$requestData["location"]["state"].
                        " ".$requestData["location"]["country"].
                        " ".$requestData["location"]["pincode"];
		$latLng=$this->get_lat_long($rec_address);
		 $latitude = $latLng['lat'];
        $longtitude = $latLng['lng'];
			}
		$matchThese = ['id' => $requestData['id']];
		//New Key added for adding Others Category
		$othersprofession= array_key_exists('others',$requestData)?$requestData['others']:"";
		//User Search ID
		$user_searchid= array_key_exists('user_searchid',$requestData)?$requestData['user_searchid']:"";
		
		$user = CM4UserProfile::where($matchThese)->get();
        $status = $user->count();

		$matchThese1 = ['contact_no' => $requestData['contact_no']];
		$user_sim = CM4UserSimno::where($matchThese1)->get();
			if(count($user_sim)!=0){
				$sim_number=$user_sim[0]->sim_number;
			}else{
				$sim_number='';
			}
		
			if ($status  != 0) {
	        $username = $requestData['contact_person'];
            $email = $requestData['email'];
            $profilePic = $data['name'];
		   // $gender = $requestData['gender'];
            //$age = $requestData['age'];
            $lat = $latitude;
            $long = $longtitude;
            $addressSource = $requestData['address_source'];
            $address = $requestData['location']['address'];
            $address2 = $requestData['location']['address2'];
            $locality = $requestData['location']['locality'];
            $city = $requestData['location']['city'];
            $state = $requestData['location']['state'];
            $country = $requestData['location']['country'];
            $pincode = $requestData['location']['pincode'];
			$profession = $requestData['profession'];
            $workPlace = $requestData['org_name'];
			$aboutMe = $requestData['about_me'];
           
			if($othersprofession=="")
				{
				$list=$this->multi_implode($requestData['profession'],",");
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				$list =implode(",",$matches[0]);	
				
				$getcategory=$this->getsearchtags_of_ids($list);	
				$category_json=serialize($profession);
				
				}
				else
				{
				$getcategory=$requestData['others'];
				 $list=$this->multi_implode($requestData['profession'],",");
				
				$list = implode(',',array_unique(explode(',', $list)));
				//return $list;
				preg_match_all('!\d+!', $list, $matches);
				if (in_array('0',$matches[0], true)) {
				$list =implode(",",$matches[0]);	
				$getcategory=$this->getsearchtags_of_ids($list);
				if($getcategory!="")
				{
				$getcategory=$getcategory.';'.$othersprofession;
				}
				else
				{
				$list=0;
				$getcategory=$requestData['others'];
				}		
			 }
				$category_json=serialize($profession);
				}	
            $current_rec = CM4UserProfile::find($user[0]['id']);
         
			$current_rec->user_name = $username;
            $current_rec->email = $email;
            $current_rec->contact_person = $username;
            if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
           // $current_rec->gender = $gender;
            //$current_rec->age = $age;
            $current_rec->latitude = $lat;
            $current_rec->longitude = $long;
            $current_rec->address_source = $addressSource;
            $current_rec->address = $address."|".$address2;
            $current_rec->city = $city;
            $current_rec->state = $state;
            $current_rec->country = $country;
            $current_rec->locality = $locality;
            $current_rec->profile_status = 1;
            $current_rec->category = $getcategory;
            $current_rec->category_ids =$list ;
            $current_rec->category_json =$category_json ;
            $current_rec->user_name = $workPlace;
			$current_rec->about_us = $aboutMe;
            $current_rec->pincode = $pincode;
            $current_rec->update_profile_status = 1;
            $current_rec->isConsultat = 1;
			
			//CHECK FOR PREMIUM USER
				
			 $current_pre = CM4PremiumUser::find($user[0]['id']);
			if(count($current_pre)>0)
			{
			$current_pre->user_name = $username;
            $current_pre->email = $email;
            $current_pre->contact_person = $username;
            if($profilePic!="") {
                $current_pre->profile_pic = $profilePic;
            }
            $current_pre->latitude = $lat;
            $current_pre->longitude = $long;
            $current_pre->address_source = $addressSource;
            $current_pre->address = $address."|".$address2;
            $current_pre->city = $city;
            $current_pre->state = $state;
            $current_pre->country = $country;
            $current_pre->locality = $locality;
            $current_pre->profile_status = 1;
            $current_pre->category = $getcategory;
            $current_pre->category_ids =$list ;
            $current_pre->category_json =$category_json ;
            $current_pre->user_name = $workPlace;
			$current_pre->about_us = $aboutMe;
            $current_pre->pincode = $pincode;
            $current_pre->update_profile_status = 1;
			$current_pre->save();
			$solrupdate=$this->_update_premium_solr($requestData['id']);
			}
			
			
			//Add Social Info to another table 
			$youtube_link=$requestData['youtube_link'];
			$facebook_link=$requestData['facebook_link'];
			$twitter_link=$requestData['twitter_link'];
			$instagram_link=$requestData['instagram_link'];
			$snapchat_link=$requestData['snapchat_link'];
			$blog_link=$requestData['blog_link'];
			$msg_bf_call=$requestData['msg_bf_call'];
			$more_about=$requestData['more_about'];
			
			$matchTheseSocial = ['uid' => $requestData['id']];
			$usersocial = CM4UserSocial::where($matchTheseSocial)->get(['uid']);
            $chkstatus = $usersocial->count();
			if($chkstatus=='0')
			{
			$socialdata = [
                     "uid"=> $requestData['id'],
					 "youtube_link"=>$youtube_link,
                     "facebook_link"=>$facebook_link,
                     "twitter_link" =>$twitter_link,
                     "instagram_link" =>$instagram_link,
                     "snapchat_link" =>$snapchat_link,
                     "blog_link" =>$blog_link,
                     "msg_bf_call" => $msg_bf_call,
					 "more_about"=>$more_about
                     ];
				 CM4UserSocial::create($socialdata);
			}
			else
			{
			
			$socialdata = [
                     "youtube_link"=>$youtube_link,
                     "facebook_link"=>$facebook_link,
                     "twitter_link" =>$twitter_link,
                     "instagram_link" =>$instagram_link,
                     "snapchat_link" =>$snapchat_link,
                     "blog_link" =>$blog_link,
                     "msg_bf_call" => $msg_bf_call,
					 "more_about"=>$more_about
                     ];
				 CM4UserSocial::where($matchTheseSocial)->update($socialdata);	
			}
		if ($current_rec->save()) {
               $userid=$user[0]['id'];
			  
			   $solrupdate=$this->_update_by_username_solr($requestData['id']);
				$matchThese = ['id' => $requestData['id']];
				$user = CM4UserProfile::where($matchThese)->get();
                //return $user;
               $searched_uid=$userid;
		       $searched_contact=$requestData['contact_no'];	
		       $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
			else
			{
			$reviewcount='0';
			$avgrating='0';	
			}
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcountquery)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
			
				$address =explode('|',$user[0]['address']);
                $finalData=[
                    'id'=>$user[0]['id'],
                    'user_id'=>$user[0]['user_id'],
                    'user_name'=>$user[0]['user_name'],
					'user_searchid'=>$user[0]['user_searchid'],
                    'org_name'=>$user[0]['user_name'],
                    'profile_pic'=>\Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'],
                    'gender'=>$user[0]['gender'],
                    'locality'=>$user[0]['locality'],
                    'address'=>$address[0],
                    'address2'=>$address[1],
                    'country'=>$user[0]['country'],
                    'city'=>$user[0]['city'],
                    'state'=>$user[0]['state'],
                    'latitude'=>$user[0]['latitude'],
                    'longitude'=>$user[0]['longitude'],
                    'call_time'=>$user[0]['call_time'],
                    'email'=>$user[0]['email'],
					'piggy_bal'  =>$user[0]['piggy_bal'],
                    'update_profile_status'  =>$user[0]['update_profile_status'],
                    'live_status'  =>$user[0]['live_status'],
                    'category_ids'  =>$user[0]['category_ids'],
                    'profile_status'  =>$user[0]['profile_status'],
                    'verification_status'  =>$user[0]['verification_status'],
                    'referal_code'  =>$user[0]['referal_code'],
                    'marital_status'  =>$user[0]['marital_status'],
                    'cc_fdail'  =>$user[0]['cc_fdail'],
                    'about_us'=>$user[0]['about_us'],
                    'user_rating'=>'',
                    'contact_person'=>$user[0]['contact_person'],
                    'contact_no'=>$user[0]['contact_no'],
                    'pincode'=>$user[0]['pincode'],
                    'service'=>$user[0]['category'],
					'per_min_val'=>$user[0]['per_min_val'],
                    'service_ids'=>unserialize($user[0]['category_json']),
                    'address_source'=>$user[0]['address_source'],
					'callcount'=>$callcount,
					'reviewcount'=>$reviewcount,
					'avgrating'=>$reviewcount,
					'youtube_link'=>$youtube_link,
					'facebook_link'=>$facebook_link,
					'twitter_link'=>$twitter_link,
					'instagram_link'=>$instagram_link,
					'snapchat_link'=>$snapchat_link,
					'blog_link'=>$blog_link,
					'msg_bf_call'=>$msg_bf_call,
					'more_about'=>$more_about,
					'simno'=>$sim_number,
					'other_contact_no'=>$user[0]->marital_status
					];
			
			
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$finalData, "device_key" => $token]);
            } else {
					$result = collect(["status" => [ "code" => "101", "message" => \Config::get('constants.results.101')],
                    "device_key" => $token]);
            }
        } else {

            $result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160'), "device_key" => $token]);

        }

        return response()->json($result, 200);


    }

    public function getMyProfile_new() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";

        if (Request::header('content-type') == "application/json") {

            $requestData = Request::json()->all();
		
		
		} else {

            $requestData = Request::all();
        }
	
	   if (!(array_key_exists('phone', $requestData)
            && array_key_exists('id', $requestData)
            
        )) {
          
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) != 2) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        } 

        $fields = [
            'phone' => $requestData['phone'],
            'id' => $requestData['id']
        ];
        $rules = [
            'phone' => 'required',
            'id' => 'required',
            
        ];
        $valid = \Validator::make($fields, $rules);
        if ($valid->fails()) {
            return [
                'status'=>'0',
                'message' => 'validation_failed',
                'errorCode'=>'',
                'errorDesc' => $valid->errors()
            ];
            
        }
		
		 //Check User Details Status
        $length=strlen($requestData['phone']);
        if($length==12){
        	$mobilenonew=$requestData['phone'];
        }else{
        	$mobilenonew='91'.$requestData['phone'];
        }
		$matchThese = ['contact_no' => $mobilenonew];
		$user = CM4UserProfile::where($matchThese)->get();

		$matchThese1 = ['contact_no' => $mobilenonew];
		$user_sim = CM4UserSimno::where($matchThese1)->get();
			if(count($user_sim)!=0){
				$sim_number=$user_sim[0]->sim_number;
			}else{
				$sim_number='';
			}

        $status = $user->count(); 
			
		 		if($status>0)
				{ 
				//Check Social Data
				$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0)
				{
				   $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}	
				else
				{
				    $youtube_link='';
					$facebook_link='';
					$twitter_link='';
					$instagram_link='';
					$snapchat_link='';
					$blog_link='';
					$msg_bf_call='';
					$more_about='';		
				}		
				
			$searched_uid=$user[0]->id;	
		     //Get Force Rate Update
		
		$today_date=date('Y-m-d');
		$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0)
			{
			$user[0]['force_close']='1';	
			}
			else
			{
			$user[0]['force_close']='0';	
			}
			 
			//Call Time Today	
			
			if($user[0]['call_time']!="") 
	       {
            $time= $this->today_timing($user[0]['call_time']);
            $time=str_replace("-","|",$time);
			$user[0]['today_timing']=$time;
           }
		 else
		  {
			$user[0]['today_timing']="";
		  }
			 
			 //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		 
		 $searched_contact=$user[0]->contact_no;	
		  $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
		  $raterevqryex= \ DB::select($rate_rev_qry);
			$reviewcount=0;
			$avgrating=0;
			if(count($raterevqryex)>0)
			{
			$reviewcount=$raterevqryex[0]->reviewcount;	
			$avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		}
				   $address =explode('|',$user[0]['address']);
                    $user[0]['is_logged_from_another_device']="0";
                    $user[0]['id']=$user[0]['id'];
                    $user[0]['org_name']=$user[0]['user_name'];

                   // $user[0]['profile_pic']="https://www.callme4.com/api/public/noImage.png";

                    if($user[0]['profile_pic']!='') {
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $user[0]['profile_pic'];
                    }else{
                        $user[0]['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
                    }
                    $user[0]['address']=$address[0];
                    $user[0]['address2']=isset($address[1])?$address[1]:"";
                    
                    $user[0]['service']=$user[0]['category'];
                    //check category_json  
					if($user[0]['category_json']!="")
						{
						$user[0]['service_ids']=unserialize($user[0]['category_json']);	
						}
					else
						{
						$user[0]['service_ids']=array();	
						}
                    $user[0]['youtube_link']=$youtube_link;
					$user[0]['facebook_link']=$facebook_link;
					$user[0]['twitter_link']=$twitter_link;
					$user[0]['instagram_link']=$instagram_link;
					$user[0]['snapchat_link']=$snapchat_link;
					$user[0]['blog_link']=$blog_link;
					$user[0]['msg_bf_call']=$msg_bf_call;
					$user[0]['more_about']=$more_about;
					$user[0]['reviewcount']=$reviewcount;
					$user[0]['avgrating']=$avgrating;
					$user[0]['callcount']=$callcount;
					$user[0]['simno']=$sim_number;
					$user[0]['other_contact_no']=$user[0]->marital_status;
					
                    unset($user[0]['category_json']);

                    $finalData=['user_registration_status'=> $user[0]['contact_person']=="" ?"0":"1",'user'=>$user[0]];
                    $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData, "device_key" => $token]);
                }
             else 
			 { 
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
			}

        return response()->json($result, 200);
    }

    public function insertblockuser(){ 
		$collection=[];
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}

		if (!(array_key_exists('blocked_by', $requestData)&& array_key_exists('blocked_to', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		}
		$matchThese = ['blocked_by'=>$requestData['blocked_by'],'blocked_to'=> $requestData['blocked_to']];
		$list = CM4BlockUser::where($matchThese)->get();
		$status = $list->count();

		if($status==0){
			$data_par=array('blocked_by'=>$requestData['blocked_by'],'blocked_to'=>$requestData['blocked_to']);
			$inserttime=CM4BlockUser::create($data_par);
             $data = collect(["status" => "1","message" => 'block user created successfully!','errorCode'=>'','errorDesc'=>'',"insertid"=>$inserttime->id,"data" =>array(), "device_key" => $token]);
        }else{ 
        	//$block_status='';
        	if($list[0]->flag_status==0){
        		$blkstatus="1";
        	}else{
        		$blkstatus="0";
        	}//print_r($blkstatus);exit();
        	$user = CM4BlockUser::find($list[0]->id);
        	$user->flag_status = $blkstatus;
			$user->save();
			if($blkstatus==1){
				$msg="User blocked successfully";
			}else{
				$msg="User unblock successfully";
			}
        	$data = collect([ "status" => "1","message" => $msg,'errorCode'=>'','errorDesc'=>'',  "device_key" => $token]);
        }
        return response()->json($data, 200);

	}

	public function getblockuser(){
		$collection=[];
		$token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}

		if (!(array_key_exists('blocked_by', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		} //print_r('d');exit();
		$matchThese = ['blocked_by'=>$requestData['blocked_by'],'flag_status'=>1];
		$list = CM4BlockUser::where($matchThese)->get(); 
		$status = $list->count();

		if($status==0){
			$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),  "device_key" => $token]);
		}else{
			$rec = \DB::table('cm4_block_user')
            ->select(\DB::raw("GROUP_CONCAT(blocked_to) as 'ids'"))
            ->where('blocked_by',$requestData['blocked_by'])
            ->where('flag_status',1)
            ->get();
			if($rec[0]->ids!=""){
				$ids = $rec[0]->ids;
	            $query="SELECT id,user_id,call_time,contact_no,contact_person,user_name,address,locality,cc_fdail,longitude,profile_pic,latitude,category as services,live_status FROM cm4_user_profile where user_id  IN ($ids ) ";
	            $userdata= \ DB::select($query);
	            $records=[];
	            foreach($userdata as $val){
					$val->user_name=trim($val->user_name);
					$val->cc_fdail=$val->cc_fdail;
					$val->user_id=$val->user_id;
					$val->contact_no=$val->contact_no;
					if($val->contact_person=="" || $val->contact_person==" ") {
						$val->contact_person= $val->user_name;
					}
					$val->latitude=$val->latitude;
					$val->longitude=$val->longitude;
					$val->address=$val->address;
					$val->locality=$val->locality;
					/*$match_cre=['blocked_by'=> $requestData['blocked_by'],'blocked_to'=>$val->user_id,'status'=>1];
                    $usertime = CM4BlockUser::where($match_cre)->get(); 
                    print_r($usertime);exit();
                    if($usertime->count()>0) {
                    	$val->block_date = $usertime[0]['created_at']->format('Y-m-d H:i:s');
                    }else{
                    	$val->block_date="";
                    }*/
                    //print_r($val->block_date);exit();
                    if ($val->call_time != "" && $val->live_status=='0') {
                        $time = $this->today_timing($val->call_time);
                         $time=str_replace("-","|",$time);
						$val->today_timing = $time;
                    }
					else
					{
					 $val->today_timing =$val->call_time;	
					}
					
					if($val->profile_pic!='') {
	                    $val->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $val->profile_pic;
	                }else{
	                    $val->profile_pic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
	                }

					array_push($records,$val);
	            }
	            $data = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'','errorDesc'=>'',"data" => $userdata
                , "device_key" => $token]);
			}else{
            $data = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
          //  return 1;
         }
		}
		return response()->json($data, 200);
	}
	
	public function getuserdetailsbyid_new() {
        $token = "f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
        if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData)
            &&array_key_exists('latitude', $requestData)
            &&array_key_exists('longitude', $requestData)
        )) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
     $userId=$requestData['uid'];
        $lat=$requestData['latitude'];
        $long=$requestData['longitude'];
    $myid=array_key_exists('myid',$requestData)?$requestData['myid']:"";
	
	$selectqry=\ DB::select("select id,user_id,contact_person,gender,profile_pic,cc_fdail,user_id,category_ids,live_status,user_name,contact_no,latitude,longitude,address,email,call_time,per_min_val,locality,user_searchid,category as service,is_callback as online_status,(6371 * ACOS (COS(RADIANS(latitude))* COS(RADIANS($lat))* COS( RADIANS( $long ) - RADIANS(longitude)) + SIN ( RADIANS(latitude) ) * SIN(RADIANS( $lat )))) AS dist from cm4_user_profile where id='".$userId."'");

	    $myuserid=\ DB::select("select user_id from cm4_user_profile where id='".$myid."'");
		//print_r($myuserid[0]->user_id);exit();
		
		if(isset($selectqry[0])>0)
		{
		if($selectqry[0]->category_ids!="")
		{
		$tags=$selectqry[0]->category_ids;
		$selecttags=\ DB::select("SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($tags) and `cm4_categories`.`type_id`=1");	
	//	echo $selecttags[0]->tags;die;
		$selectqry[0]->tags=$selecttags[0]->tags;
		
		}
		else
		{
			$selectqry[0]->tags="";
		}		
			
	 if($selectqry[0]->call_time!="") 
	 {
            $time= $this->today_timing($selectqry[0]->call_time);
             $time=str_replace("-","|",$time);
			if($time=='Closed')
			{
			$selectqry[0]->today_timing='';	
			}
			else
			{
			$selectqry[0]->today_timing=$time;
            }
		}
			else
			{
                $selectqry[0]->today_timing="";
            }	
		if(trim($selectqry[0]->contact_person)!="")
			{
			$selectqry[0]->contact_person=trim($selectqry[0]->contact_person);	
			}
			else if(trim($selectqry[0]->user_name)!="" && trim($selectqry[0]->contact_person)=="") 
			{
				$selectqry[0]->contact_person = $selectqry[0]->user_name;
            }
			else
			{
				$selectqry[0]->contact_person="";
			}
             $matchThese=['uid'=> $myid,'favid'=>$userId,'status'=>1];
			 $user = CM4UserFavourite::where($matchThese)->get();
            $selectqry[0]->favourite_status=  $user->count()>0?1:0;

            //-----------------------block user ---------------------//
             $matchThese1=['blocked_by'=> $myuserid[0]->user_id,'blocked_to'=>$selectqry[0]->user_id,'flag_status'=>1];
			 $user_block = CM4BlockUser::where($matchThese1)->get();
			 $selectqry[0]->block_status=  $user_block->count()>0?1:0;

            //-----------------------end-----------------------------//
               //GET RATING AND REVIEWS COUNT FOR SEARCHED USERS
		$searched_uid=$userId;
		$searched_contact=$selectqry[0]->contact_no;	
		 $rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0)
			{
			$selectqry[0]->reviewcount=$raterevqryex[0]->reviewcount;	
			$selectqry[0]->avgrating=$raterevqryex[0]->avgrating;
			}
		
		//Get Force Rate Update
		$today_date=date('Y-m-d');
		if($searched_uid=='999545'){
			$selectqry[0]->force_close='1';	
		}else{
			$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0){
				$selectqry[0]->force_close='1';	
			}else{
				$selectqry[0]->force_close='0';	
			}
		}
		
		//Get About Us 
		$aboutus="";
		 $getaboutus="SELECT more_about as about_us FROM `cm4_user_social_info` WHERE uid='".$searched_uid."'";
			$getaboutus_ex= \ DB::select($getaboutus);
			if(count($getaboutus_ex)>0)
			{
			$selectqry[0]->about_us=$getaboutus_ex[0]->about_us;	
			
			}
			else
			{
			$selectqry[0]->about_us=$aboutus;	
			}
		
		
		//Get Total Call call count received.
		$callcount=0;
		$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
		$callcountquery= \ DB::connection('a2billing')->select($querycategory);
		if(count($callcount)>0)
		{
		$callcount=$callcountquery[0]->totalcount;
		$selectqry[0]->callcount=$callcount;
		}

            if($selectqry[0]->profile_pic!='') {
                $selectqry[0]->profile_pic = \Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;
            }else{
                $selectqry[0]->profile_pic = "" ;
            }
		//Video Status
		$video_status=CM4PremiumUser::where('id', '=',$userId)->where('video_id', '!=','')->get();
		$selectqry[0]->is_video=0;
		$selectqry[0]->video_id='';
		$selectqry[0]->video_title='';
		$selectqry[0]->Is_Youtube='0';
		
		if($video_status->count()>0)
		{
		$selectqry[0]->is_video=1;
		$selectqry[0]->video_id=$video_status[0]->video_id;	
		$selectqry[0]->video_title=$video_status[0]->video_title;	
		$selectqry[0]->Is_Youtube=$video_status[0]->Is_Youtube;
		}
		//Get OfferRate
		 $selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$userId."'");	
		    if(count($selectofferrate)>0)
			{
			$selectqry[0]->offer_rate=$selectofferrate[0]->offer_rate;	
			}
			else
			{
			$selectqry[0]->offer_rate='';		
			}
  
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$selectqry[0], "device_key" => $token]);

        } else {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);

        }

        return response()->json($result, 200);
    }

}
