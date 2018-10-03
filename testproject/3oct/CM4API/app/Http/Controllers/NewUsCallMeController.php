<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Test;
/*use JWTAuth;
use JWTFactory;
use JWTAuthException;*/
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
use App\UsBankAccount;
class NewUsCallMeController extends Controller
{

	public static $ctr=0;

	public function __construct()
	{
	//$this->middleware('oauth');
	}


	public function getcode(){
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		$fields = [
			'country_code' => $requestData['country_code'],
			'mobile' => $requestData['mobile'],
			'fcmToken' => $requestData['fcmToken']
		];
		$rules = [
			'country_code' => 'required|numeric',
			'mobile' => 'required|phone',
			'fcmToken' => 'required'
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
		if (!(array_key_exists('country_code', $requestData) && array_key_exists('mobile', $requestData) && array_key_exists('fcmToken', $requestData))) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" =>""]);
			return $result;
		}

		$latitude=0;
		$longitude=0;
		$city="";
		$state="";
		$city1="";
		$state1="";
		$msg="";
		$token="";
		$status="";
		if(array_key_exists("latitude",$requestData) && array_key_exists("longitude",$requestData) &&array_key_exists("city",$requestData) && array_key_exists("state",$requestData)){
			$latitude=$requestData['latitude'];
			$longitude=$requestData['longitude'];
			$city=$requestData['city'];
			$state=$requestData['state'];
		}
		$phoneNumber = $requestData['country_code'].$requestData['mobile'];
		$matchThese = ['phone' => $phoneNumber];
		$userinfo = CM4UserInfo::where($matchThese)->get(['id','phone','device_id','city']);
		$userCount=$userinfo->count();  
		if($userCount==0){
			$code=$this->rand_string(4);
            $this->sendsms($phoneNumber, $code);
            if($requestData['fcmToken']){
            	$fcmToken=$requestData['fcmToken'];
            }else{
            	$fcmToken='';
            }
			$data = array("phone" =>$phoneNumber,
			"c_code" => $requestData['country_code'],
			"device_id" => $fcmToken,
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
			//$user = CM4UserInfo::first();
        	$token = '';

			$msg="User is Created Successfully.";	
			$status='1';
		}else{
			//if(trim($requestData['fcmToken'])==trim($userinfo[0]->device_id)){ //print_r('d');exit();
				$code=$this->rand_string(4);
				$contact_no=$requestData['mobile'];
				sleep(3);
				$this->sendsms($phoneNumber, $code);
				$current_rec = CM4UserInfo::find($userinfo[0]['id']);
				$current_rec->code = $code;
				$current_rec->save();
				//$user = CM4UserInfo::first();
        		$token = '';
				$status='1';
				$msg="OTP sent Successfully.";	
			//}
		}

		if($status=='1'){
			$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
			$result = collect(["status" => "1", "message" =>$msg,"token" => $token]);
		}else{
			$data=["sms_code"=>'Hi,Tester Kindly give some more bugs.'];
			$result = collect(["status" => "0", "message" =>$msg,"token" => $token]);   
		}
		return response()->json($result, 200);
	}


	//------------------verify otp--------------------------//
	public function verify_otp(){ 
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}
		if(!(array_key_exists('mobile', $requestData) && array_key_exists('code', $requestData) )) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		}
		$fields = [
            'mobile' => $requestData['mobile'],
            'code' => $requestData['code'],
        ];
        $rules = [
            'mobile' => 'required',
            'code' => 'required|numeric|digits:4',
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
        $matchThese_de = ['contact_no' => $requestData['mobile']];
		$userdetails = CM4UsersDetails::where($matchThese_de)->get(['id']);
        $userdetailsstatus = $userdetails->count();

        $mobile_no=$requestData['country_code'].$requestData['mobile'];

        $matchThese_info = ['phone' =>$mobile_no,'code'=>$requestData['code']];
        $user_info = CM4UserInfo::where($matchThese_info)->get(['id']);
        $status = $user_info->count();  //print_r($status);exit();

		if($status != 0){
			$user_info = CM4UserInfo::find($user_info[0]['id']);
			$user_info->status = 1;
            $user_info->save();

            $matchThese = ['contact_no' => $user_info->phone];
            $user = CM4UserProfile::where($matchThese)->get();
            $status = $user->count();
			$phone=$requestData['mobile'];
			$country_code= $requestData['country_code'];
            $email="";
            $name=""; 
            if($status==0){  
            	$referal=$this->gen_referal_code();//print_r($referal);exit();
                $genData= $this->register($phone,$email,$name,$country_code); 
				$userId=$genData['user_id'];
				$fdial=$genData['cc_fdial'];
				$pass=$genData['cc_password'];
				$piggybal="5.00";   
				$category_json="a:0:{}";
				$userinfo=array('user_id'=>$userId,'user_name'=>'','gender'=>'','age'=>0,'contact_no'=>$matchThese['contact_no'],'email'=>'','about_us'=>'','city'=>$user_info->city,'state'=>$user_info->state,'locality'=>$user_info->longitude,'address'=>'','category'=>'','category_ids'=>0,'category_json'=>$category_json,'data_source'=>'','profile_pic'=>'','call_time'=>'','latitude'=>$user_info->latitude,'longitude'=>'','cc_password'=>$pass,'cc_fdail'=>$fdial,'verification_status'=>'0','live_status'=>'1','created_at'=>date('Y-m-d H:i:s'),'pincode'=>'','referal_code'=>'','piggy_bal'=>$piggybal,'is_installed'=>'1','c_code'=>$requestData['country_code']);
				//print_r($userinfo);exit();
				$data=CM4UserProfile::create($userinfo);

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
				$check_user=['uid' =>$data->id];	
				$Refer = CM4UserRefer::where($check_user)->get(['id']);
				if(count($Refer)==0){
					$cm4_user_refers=array('uid'=>$data->id,'refer_code'=>$referal,'earned_by_uid'=>'','created_at'=>date('Y-m-d H:i:s'));	  
					$datarefer=CM4UserRefer::create($cm4_user_refers);
				}
				$finalData=['user_registration_status'=>'0','user'=>['id'=>$data->id,'user_id'=>$userId]];
                $result = collect(["status" => "1", "message" => \Config::get('constants.results.112'),'errorCode'=>'','errorDesc'=>'','data'=>$finalData]);
            }else{
            	$contact_no=$requestData['country_code'].$requestData['mobile'];
            	CM4UserProfile::where('contact_no',$contact_no)->update(['live_status' =>1,'is_installed'=>1]);

            	$matchThese2 = ['contact_no' => $user_info->phone];
            	$user_lsearchid = CM4UserProfile::where($matchThese2)->get();

            	$status_serach = $user_lsearchid->count();

            	$matchTheseSocial = ['uid' => $user[0]->id];
				$usersocial = CM4UserSocial::where($matchTheseSocial)->get();
                $chkstatus = $usersocial->count();
				if($chkstatus>0){
				    $youtube_link=$usersocial[0]->youtube_link;
					$facebook_link=$usersocial[0]->facebook_link;
					$twitter_link=$usersocial[0]->twitter_link;
					$instagram_link=$usersocial[0]->instagram_link;
					$snapchat_link=$usersocial[0]->snapchat_link;
					$blog_link=$usersocial[0]->blog_link;
					$msg_bf_call=$usersocial[0]->msg_bf_call;
					$more_about=$usersocial[0]->more_about;	
				}else{
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
				$searched_contact=$user[0]->contact_no;	
				$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
				$raterevqryex= \ DB::select($rate_rev_qry);
				$reviewcount=0;
				$avgrating=0;
				if(count($raterevqryex)>0){
					$reviewcount=$raterevqryex[0]->reviewcount;	
					$avgrating=$raterevqryex[0]->avgrating;
				}
				$callcount=0;
				$querycategory="SELECT count(*) as totalcount from cc_call where calledstation='".$searched_contact."'";
				$callcountquery= \ DB::connection('a2billing')->select($querycategory);
				if(count($callcount)>0){
					$callcount=$callcountquery[0]->totalcount;
				}
				
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
                    $user[0]['service']=$user[0]['category'];
                    if($user[0]['category_json']!=""){
						$user[0]['service_ids']=json_decode($user[0]['category_json']);	
					}else{
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
					if(!empty($user_lsearchid[0]->user_searchid)){
						$user_registration_status=1;
					}else{
						$user_registration_status=0;
					}
					$finalData=['user_registration_status'=>$user_registration_status,'user'=>['id'=>$user[0]->id,'user_id'=>$user[0]->user_id]];
                    
                    $result = collect(["status" => "1", "message" =>'User verifyed successfully','errorCode'=>'','errorDesc'=>'','data'=>$finalData]);
						
            }
		}else{
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.113'),'errorCode'=>'','errorDesc'=>'']);
		}
		return response()->json($result, 200);
	}
	//------------------end --------------------------------//


	//----------------update name----------------------------//
	public function usupdateName(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}
		if(!(array_key_exists('uid', $requestData) && array_key_exists('fullName', $requestData) && array_key_exists('userName', $requestData) && array_key_exists('image', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
            return $result;
		}
		$fields = [
			'uid' => $requestData['uid'],
			'fullName' => $requestData['fullName']
		];
		$rules = [
			'uid' => 'required',
			'fullName' => 'required'
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
		$matchThese=['id' => $requestData['uid']];
		//$mycontact=$requestData['mobile'];
		$user = CM4UserProfile::where($matchThese)->get();
		$status = $user->count();
		if($user->count()!= 0){
			$name = $requestData['fullName'];
			$data=   $this->imageUpload($requestData['image']);
			$profilePic = $data['name'];
			$user_searchid= array_key_exists('userName',$requestData)?$requestData['userName']:"";
			$current_rec = CM4UserProfile::find($user[0]->id);
            $current_rec->contact_person =$name;
            $current_rec->user_name =$name;
            if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
			$current_rec->user_searchid =$user_searchid;
			$current_rec->save();
			CM4PiggyBankAccount::where('uid',$user[0]->id)->update(['user_name' =>$name]);
			$finalData=['user_registration_status'=>'1'];
			$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),"user_registration_status"=>1]);
		}else{
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.160'),'errorCode'=>'160','errorDesc'=>\Config::get('constants.results.160')]);
		}
		return response()->json($result, 200);
	}
	//----------------end -----------------------------------//

	//-------------------DASHBAORD---------------------------//
	public function usdashboard(){ 
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if(!(array_key_exists('uid', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
            return $result;
        }
        $userId=$requestData['uid'];
        $userappversion="";
		$latestversion="116";
		if(array_key_exists('version_code',$requestData)){
			$userappversion=$requestData['version_code'];
			$userid=$requestData['uid'];
			$matchThese = ['user_id' => $requestData['uid']];
			$appinfo = CM4UserVersion::where($matchThese)->get();
			$statuscount = $appinfo->count();
			if($statuscount==0){
				$appvesion =[
				'user_id' => $requestData['uid'],
				'user_app_version' => $userappversion,
				];	
				CM4UserVersion::create($appvesion);
			}else{
				CM4UserVersion::where('user_id',$userid)->update(['user_app_version' =>$userappversion]);	
			}

		}
        $today_date =date('Y-m-d');
        $selectqry=\ DB::select("select id as uid,user_searchid,user_id,isConsultat,profile_pic,user_name as userName,contact_no,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,call_time,category_json,per_min_val as callRate,age as alldaytype,cc_fdail from cm4_user_profile where id=".$userId."");
        $useridd='';
        if($selectqry){
        	$useridd=$selectqry[0]->user_id;
	        $selectqry[0]->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;

	        $selectfav=\ DB::select("select id as uid,user_id,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where id in (select favid from cm4_user_favourite where uid = ".$userId." and status=1)");
	        foreach ($selectfav as $key => $value) {
	        	$value->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
	        }
	        $selectqry[0]->bookMarkUser=$selectfav;

	        $selectblock=\ DB::select("select id as uid,user_id,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where user_id in (select blocked_to from cm4_block_user where blocked_by = ".$selectqry[0]->user_id." and flag_status=1)");
	        foreach ($selectblock as $key => $val) {
	        	$val->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $val->profile_pic;
	        }
	        $qry1_tocall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->mobile." and date(starttime ) BETWEEN '".date('Y-m-d')."' and '".date('Y-m-d')."'";
	        $datatod= \DB::connection('a2billing')->select($qry1_tocall);
	        $todaycall='';
	        if($datatod){
	        	$todaycall=$datatod[0]->calltotal;
	        }

	        $qry1_totcall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->mobile."";
	        $datatot= \DB::connection('a2billing')->select($qry1_totcall);
	        $totalcall='';
	        if($datatot){
	        	$totalcall=$datatot[0]->calltotal;
	        }

	        if($selectqry[0]->isConsultat==1){
				$consocial=\ DB::select("select * from cm4_user_social_info where uid =".$selectqry[0]->uid.""); 
				if($consocial){
					$selectqry[0]->about=$consocial[0]->more_about;
					$SocialModel=array('youtube'=>$consocial[0]->youtube_link,'facebook'=>$consocial[0]->facebook_link,'twitter'=>$consocial[0]->twitter_link,'instagram'=>$consocial[0]->instagram_link,'snapchat'=>$consocial[0]->snapchat_link,'customlink'=>$consocial[0]->custom_link);
					$sociallist='';
				foreach ($SocialModel as $key => $value) {
					$sociallist[]=array('name'=>$key,'link'=>$value,'clickCount'=>'');
				}
					$selectqry[0]->SocialModel=$sociallist;
				}else{
					$selectqry[0]->about='';
					$selectqry[0]->SocialModel=array();
				}
				$selectqry[0]->scheculeList=$selectqry[0]->call_time;
				/*$listcat=explode(',', $selectqry[0]->category);
				$catid=explode(',', $selectqry[0]->category_ids);
				//$catid_p=explode(',', $selectqry[0]->category_parent_id);
				$category_all=array();
				if($catid[0]!='0'){
				foreach ($catid as $kc => $valct) { //$listcat[$kc];
					$category_all[]=array('professionName'=>'','professionId'=>$valct,'professionParentId'=>'');
				}
				}*/
				$selectqry[0]->listOfProfession=json_decode($selectqry[0]->category_json);

				$is_offer=0;
				$checkdate="SELECT count(*) as num FROM `cm4_user_offers` where  ((CURDATE() between offer_start_date and offer_end_date) or (CURDATE()<=offer_start_date))  and uid='".$selectqry[0]->uid."' and is_active!='2'";
				$qrychkdate= \ DB::select($checkdate);
				if(!empty($qrychkdate)){
					if($qrychkdate[0]->num > 0){
						$is_offer=1;
					}
				}
				$selectqry[0]->is_offer=$is_offer;

				$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$selectqry[0]->uid."' and CURDATE() between offer_start_date and offer_end_date");	
				if(count($selectofferrate)>0){
					$selectqry[0]->offer_rate=$selectofferrate[0]->offer_rate;	
				}else{
					$selectqry[0]->offer_rate='';		
				}
	        }

	        $selectqry[0]->todaycall=$todaycall;
	        $selectqry[0]->totalcall=$totalcall;
	        $selectqry[0]->reportNBlock=$selectblock;
	    }

	        $ratevalue=array('1'=>1,"2"=>2,"3"=>3,"4"=>4,"5"=>5,"6"=>10,"7"=>15,"8"=>20,"9"=>30,"10"=>40,"11"=>50,"12"=>70,"13"=>100,"14"=>150,"15"=>200);
	        //$ratevalue =array('1'=>1,"2"=>2,"3"=>3,"4"=>4,"5"=>5,"6"=>10,"7"=>15,"8"=>20,"9"=>30,"10"=>40,"11"=>50,"12"=>70,"13"=>100,"14"=>150,"15"=>200);
	        foreach ($ratevalue as $kr => $valrate) {
	        	$ratelist[]=array('id'=>$kr,'rate'=>$valrate);
	        }
	        $issuevalue=array('1'=>"Abusing on Call","2"=>"Not Skilled","3"=>"Fake Phone","4"=>"This account has been hacked","5"=>"Pretending to be me","6"=>"Profile info/image include abusive on hateful content");
	        foreach ($issuevalue as $kis => $valis) {
	        	$issueList[]=array('id'=>$kis,'title'=>$valis);
	        }
	        $subjectvalue=array('1'=>"Abusing on Call","2"=>"Not Skilled","3"=>"Fake Phone","4"=>"This account has been hacked","5"=>"Pretending to be me","6"=>"Profile info/image include abusive on hateful content");
	        foreach ($subjectvalue as $ks => $valsf) {
	        	$subjectList[]=array('id'=>$ks,'title'=>$valsf);
	        }
	        foreach ($ratevalue as $kr => $valrate) {
	        	$ratelist[]=array('id'=>$kr,'rate'=>$valrate);
	        }
	        $selectacc=\ DB::select("select id as accountId,account_number as accountNumber,routing_number as ifscCode,account_holder as holderName from us_bank_account where user_id=".$userId."");
	        if($selectacc){
	        	$accountdetails=$selectacc;
	        }else{
	        	$accountdetails=array();
	        }
	        $acoountList=$accountdetails;

	        $selectcategory=\ DB::select("SELECT `category_ids` FROM `cm4_premium_customer`");
	        $categorynewid='';
	        foreach ($selectcategory as $key => $vct) {
	        	$categorynewid.= $vct->category_ids;
	        }
	        $categorynewid = implode(',',array_unique(explode(',', $categorynewid)));
	        
	        $categorydata=\ DB::select("SELECT id,category_name FROM `cm4_categories` WHERE parent_id=0 and id in (".$categorynewid.") ");

	        foreach ($categorydata as $key => $vacategory) {
	        	$selectcons=\ DB::select("select id as uid,user_searchid,user_id,isConsultat,category_json,per_min_val as callRate,call_time,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,age as alldaytype,cc_fdail,category_ids,video_id,video_title,online_status,Is_youtube,is_verified from cm4_premium_customer where FIND_IN_SET(".$vacategory->id.",`category_ids`) and video_id!='' and isConsultat=1 order by RAND()");
	        	if($selectcons){
	        		foreach($selectcons as $key => $cons){ 
						$cons->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $cons->profile_pic;
						$conselectfav=\ DB::select("select id as uid,isConsultat,user_searchid,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where id in (select favid from cm4_user_favourite where uid = ".$cons->uid." and status=1)");
						foreach ($conselectfav as $key => $cf) {
							$cf->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $cf->profile_pic;
						}
						$cons->bookMarkUser=$conselectfav;
						$conselectblock=\ DB::select("select id as uid,user_id,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where user_id in (select blocked_to from cm4_block_user where blocked_by = ".$cons->user_id." and flag_status=1)");
						foreach ($conselectblock as $key => $cval) { 
							$cval->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $cval->profile_pic;
						}
						$cons->reportNBlock=$conselectblock;
						$cons->listOfProfession=json_decode($cons->category_json);
						$consocial=\ DB::select("select * from cm4_user_social_info where uid =".$cons->uid.""); 
						if($consocial){
							$cons->about=$consocial[0]->more_about;
							$SocialModel=array('youtube'=>$consocial[0]->youtube_link,'facebook'=>$consocial[0]->facebook_link,'twitter'=>$consocial[0]->twitter_link,'instagram'=>$consocial[0]->instagram_link,'snapchat'=>$consocial[0]->snapchat_link,'customlink'=>$consocial[0]->custom_link);
							$sociallist='';
							foreach ($SocialModel as $key => $value) {
								$sociallist[]=array('name'=>$key,'link'=>$value,'clickCount'=>'');
							}
							$cons->SocialModel=$sociallist;
						}else{
							$cons->about='';
							$cons->SocialModel=array();
						}
						if($cons->call_time){
							$sclist=$cons->call_time;
						}else{
							$sclist='';
						}
						$cons->scheculeList=$sclist;
						$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$cons->uid."'";
						$raterevqryex= \ DB::select($rate_rev_qry);
						if(count($raterevqryex)>0){
							$cons->avgrating=$raterevqryex[0]->avgrating;
						}else{
							if(count($raterevqryex)>0){
								$cons->avgrating='0';
							}	
						}
						$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$cons->uid."' and CURDATE() between offer_start_date and offer_end_date");	
						if(count($selectofferrate)>0){
							$cons->offer_rate=$selectofferrate[0]->offer_rate;	
						}else{
							$cons->offer_rate='';		
						}
						$isBlocked=\ DB::select("select blocked_to from cm4_block_user where blocked_by = ".$useridd." and blocked_to = ".$cons->user_id." and flag_status=1");
						if($isBlocked){
							$cons->isBlocked='true';
						}else{
							$cons->isBlocked='false';
						}

						$isBookmarked=\ DB::select("select favid from cm4_user_favourite where uid = ".$requestData['uid']." and favid=".$cons->uid." and status=1");
						if($isBookmarked){
							$cons->isBookmarked='true';
						}else{
							$cons->isBookmarked='false';
						}
						if($cons->video_id!='') {
							$cons->thumbnail_big="https://i.ytimg.com/vi/$cons->video_id/sddefault.jpg";
							$cons->video_id="https://www.youtube.com/watch?v=$cons->video_id";
							$cons->videoTitle=$cons->video_title;
						}else{
							$cons->video_id="";
							$cons->thumbnail_big="";
							$cons->videoTitle='';
						}
						$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$cons->uid."' and date(created_at)='".$today_date."' and online_status='0'";
						$force_ex= \ DB::select($getforce_timeset);
						if(count($force_ex)>0){
							$cons->onlineStatus='1';	
						}else{
							$cons->onlineStatus='0';	
						}		
	        		}
	        		$vacategory->Consultants=$selectcons;
	        	}
	        	
	        }//exit();

	        

	        $result = collect(["status" => "1", "message" => 'Home Feeds Data', 'userData' => $selectqry[0], 'rateList' =>$ratelist, "subjectList" =>$subjectList,"acoountList"=>$acoountList,"issueList"=>$issueList,"cunsultantData"=>$categorydata]);
                return $result;
    	

        //print_r($selectcons);exit();
	}
	//------------------END----------------------------------//


	//---------------call me 4 offer ---------------------------//
	public function uscm4offers(){
		$token="";
		if(Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('uid', $requestData))) {
			$result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')]]);
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
        $uid=$requestData['uid'];
        $rec_qry="SELECT id as offerId,per_min_val as currentCallRate,offer_rate as offerRate,offer_start_date as offerStartDate,offer_end_date as offerEndDate,status as activeStatus,is_active FROM `cm4_user_offers` where uid='".$uid."' order by offer_start_date";
        $rec= \ DB::select($rec_qry); 
        $records=array();		
		if(count($rec)>0){
			foreach($rec as $val){ 
				array_push($records,$val);	 
			} //print_r($records);
			$data = collect(["status" => "1", "message" => 'Rate Offer List!', "offerList" =>$records]);
		}else{
			$data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105'),"data" =>array()]);
		}

        return response()->json($data, 200);
	}
	//----------------end --------------------------------------//

	//---------------create offer------------------------------//
	public function uscreateNewOffer(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('uid', $requestData) && array_key_exists('offerRate', $requestData) && array_key_exists('offerStartDate', $requestData) && array_key_exists('offerEndDate', $requestData) && array_key_exists('action', $requestData)
		)) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		} 
		if($requestData['action']=="0"){
			$today_date =date('Y-m-d');
			$startdate=$requestData['offerStartDate'];
			$enddate=$requestData['offerEndDate'];
			$uid=$requestData['uid'];
			$today_date= date('Y-m-d', strtotime($today_date));
			$offer_start_date=date('Y-m-d',strtotime($requestData['offerStartDate']));
			$offer_end_date=date('Y-m-d',strtotime($requestData['offerEndDate']));
			$is_active=0;
			$checkdate="SELECT count(*) as num FROM `cm4_user_offers` where (offer_start_date <= '".$enddate."' and offer_end_date >= '".$startdate."') and uid='".$uid."' and is_active!='2'";
			$qrychkdate= \ DB::select($checkdate); 
			if($qrychkdate[0]->num==0){
				if ($today_date >= $offer_start_date && $today_date <= $offer_end_date){
					$is_active=1;
				}
				$data = [
					"uid" => $requestData['uid'],
					"per_min_val" => $requestData['currentCallRate'],
					"offer_rate" =>$requestData['offerRate'],
					"offer_start_date" =>$requestData['offerStartDate'],
					"offer_end_date" =>$requestData['offerEndDate'],
					"is_active"=>$is_active,
					"created_by"=>'1'
				];
				$insertrec=CM4Usersoffers::create($data);  
				$offerdata=array('offerId'=>$insertrec->id,'currentCallRate'=>$requestData['currentCallRate'],'offerRate'=>$requestData['offerRate'],'offerStartDate'=>$requestData['offerStartDate'],'offerEndDate'=>$requestData['offerEndDate'],'activeStatus'=>$is_active);
				if($insertrec){
					$rec_qry="SELECT id as offerId,per_min_val as currentCallRate,offer_rate as offerRate,offer_start_date as offerStartDate,offer_end_date as offerEndDate,status as activeStatus,is_active FROM `cm4_user_offers` where id='".$insertrec->id."'";
					$rec= \ DB::select($rec_qry);
					$data = collect(["status" => "1","message" => 'Offer created successfully!',"offer" =>$rec[0]]);
				}
				else{
					$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105')]);	
				}
			}else{
				$data = collect([ "status" => "0","message" => 'Date already exist in another offer created by you choose another date!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105')]);
			}	
			return response()->json($data, 200);
		}elseif($requestData['action']=="1") { 
			$today_date =date('Y-m-d');
			$startdate=$requestData['offerStartDate'];
			$enddate=$requestData['offerEndDate'];
			$uid=$requestData['uid'];
			$today_date= date('Y-m-d', strtotime($today_date));
			$offer_start_date=date('Y-m-d',strtotime($requestData['offerStartDate']));
			$offer_end_date=date('Y-m-d',strtotime($requestData['offerEndDate']));
			$is_active=0;
			$offer_id=$requestData['offerId'];
			if ($today_date >= $offer_start_date && $today_date <= $offer_end_date){
				$is_active=1;
			}
			$data = [
				"uid" => $requestData['uid'],
				"per_min_val" => $requestData['currentCallRate'],
				"offer_rate" =>$requestData['offerRate'],
				"offer_start_date" =>$requestData['offerStartDate'],
				"offer_end_date" =>$requestData['offerEndDate'],
				"is_active"=>$is_active,
				"created_by"=>'1'
			]; 
			$updateoffers=CM4Usersoffers::where('id','=',$offer_id)->update($data);  //print_r($offer_id);exit(); 
			if($updateoffers){
				$rec_qry="SELECT id as offerId,per_min_val as currentCallRate,offer_rate as offerRate,offer_start_date as offerStartDate,offer_end_date as offerEndDate,status as activeStatus,is_active FROM `cm4_user_offers` where id='".$offer_id."'";
				$rec= \ DB::select($rec_qry);
				$data = collect(["status" => "1","message" => 'Your have successfully Updated!',"offer" =>$rec[0]]);
			}
			else{
				$data = collect([ "status" => "0","message" => 'Unable to process your request!','errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105')]);	
			}
			return response()->json($data, 200);
		}elseif($requestData['action']=="2"){ 
			$offer_id=$requestData['offerId'];
	  		$status='Cancel';
	  		$offer_cancel=\DB::table('cm4_user_offers')->where('id', '=',$offer_id)->update(array('status' =>$status,'is_active'=>2)); 
			if($offer_cancel){ 
				$rec_qry="SELECT id as offerId,per_min_val as currentCallRate,offer_rate as offerRate,offer_start_date as offerStartDate,offer_end_date as offerEndDate,status as activeStatus,is_active FROM `cm4_user_offers` where id='".$offer_id."'";
				$rec= \ DB::select($rec_qry); 
				$msg='Your Offer has been Canceled';	
				$data = collect(["status" => "1", "message" => $msg,"offer" =>$rec[0]]);
			}else {
				$data = collect([ "status" => "0","message" =>'','errorCode'=>'105','errorDesc'=>'',"data" =>array()]);
			}
	  		return response()->json($data, 200);
		}else{
			$offer_id=$requestData['offerId'];
	  		$status='Stopped';
	  		$offer_cancel=\DB::table('cm4_user_offers')->where('id', '=',$offer_id)->update(array('status' =>$status,'is_active'=>2)); 
			if($offer_cancel){ 
				$rec_qry="SELECT id as offerId,per_min_val as currentCallRate,offer_rate as offerRate,offer_start_date as offerStartDate,offer_end_date as offerEndDate,status as activeStatus,is_active FROM `cm4_user_offers` where id='".$offer_id."'";
				$rec= \ DB::select($rec_qry); 
				$msg='Your Offer has been Canceled';	
				$data = collect(["status" => "1", "message" => $msg,"offer" =>$rec[0]]);
			}else {
				$data = collect([ "status" => "0","message" =>'','errorCode'=>'105','errorDesc'=>'',"data" =>array()]);
			}
	  		return response()->json($data, 200);
		}
	}
	//---------------get sms top------------------------//

	//------------------------feadback----------------------------//
	public function usCM4UserFeadback(){
		$token="";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

		if (!(array_key_exists('uid', $requestData) && array_key_exists('subjectId', $requestData) && array_key_exists('feedback', $requestData))) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		}

		$checkdate="SELECT contact_no,contact_person from cm4_user_profile where id=".$requestData['uid']."";
		$qrychkdate= \ DB::select($checkdate); 

		$data = [
			"uid" => $requestData['uid'],
			"contact_no" => $qrychkdate[0]->contact_no,
			"contact_person" =>$qrychkdate[0]->contact_person,
			"subject" => $requestData['subjectId'],
			"comments" => $requestData['feedback'],
			"app_version" => $requestData['appversion']
		];
		$insertrec=CM4UserFeadback::create($data);  
		if($insertrec){
			$data = collect(["status" => "1","message" => \Config::get('constants.results.100')]);
		}
		else{
			$data = collect([ "status" => "0","message" => \Config::get('constants.results.101'),'errorCode'=>'105','errorDesc'=>\Config::get('constants.results.105')]);	
		}
		return response()->json($data, 200);
	}
	//------------------------end --------------------------------//

	//----------------------block user----------------------------//
	public function usinsertblockuser(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('uid', $requestData) && array_key_exists('blockPersonId', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		}

		$matchThese = ['blocked_by'=>$requestData['uid'],'blocked_to'=> $requestData['blockPersonId']];
		$list = CM4BlockUser::where($matchThese)->get();
		$status = $list->count();

		if($status==0){
			$data_par=array('blocked_by'=>$requestData['uid'],'blocked_to'=>$requestData['blockPersonId'],'block_issue'=>$requestData['issueId'],'issue_comment'=>$requestData['comment']);
			//print_r($data_par);exit();
			$inserttime=CM4BlockUser::create($data_par);
			$data = collect(["status" => "1","message" => 'block user created successfully!',"data"=>'true']);
		}else{ 
			if($list[0]->flag_status==0){
				$blkstatus="1";
				$blkstatus1="true";
			}else{
				$blkstatus="0";
				$blkstatus1="false";
			}
			$user = CM4BlockUser::find($list[0]->id);
			$user->flag_status = $blkstatus;
			$user->save();
			if($blkstatus==1){
				$msg="User blocked successfully";
			}else{
				$msg="User unblock successfully";
			}
			$data = collect([ "status" => "1","message" => $msg,"data"=>$blkstatus1]);
		}
		return response()->json($data, 200);
	}

	//----------------------end-----------------------------------//

	//-----------------bookmark-----------------------------------//
	public function usmarkFavourite(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if(!(array_key_exists('uid', $requestData) && array_key_exists('bookmarkPersonId', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400')]);
			return $result;
		}

		if(count($requestData)!= 2) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400')]);
			return $result;
		} 

		$fields = [
			'uid' => $requestData['uid'],
			'bookmarkPersonId' => $requestData['bookmarkPersonId'],
		];
		$rules = [
			'uid' => 'required',
			'bookmarkPersonId' => 'required',
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
		$created_date = \Carbon\Carbon::today();
		$matchThese = ['uid' => $requestData['uid'], 'favid' => $requestData['bookmarkPersonId']];
		$userInfo   = CM4UserFavourite::where($matchThese)->get(['id','status']);
		$status     = $userInfo->count();
		if($status != 0){
			$current_rec = CM4UserFavourite::find($userInfo[0]['id']);
			$current_rec->status = $userInfo[0]['status']==1? 0: 1;
			$current_rec->save();
			$userInfo = CM4UserFavourite::where($matchThese)->get();
			if($userInfo[0]->status==1){
				$favst='true';
			}else{
				$favst='false';
			}
			$result = collect(["status" => "1","data"=>$favst, "message" => \Config::get('constants.results.100')]);
		}else{
			$data = [
				"uid" => $requestData['uid'],
				"favid" => $requestData['bookmarkPersonId'],
				"updated_at" => $created_date,
				"created_at" => $created_date
			];
			$userInfo=    CM4UserFavourite::create($data);
			$userInfo = CM4UserFavourite::where($matchThese)->get();
			$result = collect(["status" => "1","data"=>'true', "message" => \Config::get('constants.results.100')]);
		}
		return response()->json($result, 200);
	}
	//-----------------end ---------------------------------------//

	//------------------rating------------------------------------//
	public function usaddRatingReview(){
		$token="";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        }else{
            $requestData = Request::all();
        }

        if($requestData['uid'] && $requestData['ratingUserId']){
			$ratinggiventouid=$requestData['ratingUserId'];
			$data = [
				'given_by_uid' =>$requestData['uid'],
				'given_to_uid' => $requestData['ratingUserId'],
				'given_by_contact' => $requestData['callbycontact'],
				'given_to_contact' => $requestData['calltocontact'],
				'rating' => isset($requestData['rating'])?$requestData['rating']:'0',
				'comments' =>isset($requestData['msg']) ? $requestData['msg'] :'',
				'type' =>isset($requestData['userType']) ? $requestData['userType'] :'',
			];
			$data = CM4ReviewRating::create($data);
			$id = ['id' =>$data->id];
			$getaveragequery="SELECT COALESCE(SUM(rating)/COUNT(*), 0) AS avgrating  FROM `cm4_rating_review` WHERE rating >0 and given_to_uid='".$ratinggiventouid."'";
			$getaverage= \ DB::select( $getaveragequery);
			$totalavg=$getaverage[0]->avgrating;
			\ DB::statement("UPDATE cm4_user_profile SET user_rating ='".$totalavg."' where id='".$ratinggiventouid."'");
			$result = collect(["status" => "1", "message" => 'Successfully Submited..']);
            return response()->json($result, 200);
        }else{
        	$result = collect(["status" => "0", "message" => \Config::get('constants.results.400')]);
            return response()->json($result, 200);
        }
	}
	//------------------end----------------------------------------//

	//--------------------upload video ------------------------------//
	public function usuploadvideo(){
		$token="";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        }else{
            $requestData = Request::all();
        }

        if(!(array_key_exists('uid', $requestData) && array_key_exists('description', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400')]);
			return $result;
		}

		$select_video="SELECT count(id) as total,id from cm4_premium_customer where user_id='".$requestData["uid"]."'";
		$getvideo= \ DB::select($select_video); //print_r($getvideo);exit();
		if($getvideo[0]->total!=0){
			if($requestData["url"]){
				$premium_update=\DB::table('cm4_premium_customer')->where('id', '=',$getvideo[0]->id)->update(array('video_id'=>$requestData['url'],'video_title'=>$requestData['description']));
				$videodata=array('videoId'=>$requestData['url'],'videoDescription'=>$requestData['description']);
				$result = collect(["status" => "1", "message" => 'Updated!', "video" =>$videodata]);
				return response()->json($result, 200);
			}else{
				print_r('dd'); exit();
			}
			
		}else{
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400')]);
            return response()->json($result, 200);
		}
		

		
	}

	//----------profile update----------------------------------------//
	public function usupdateUserProfile(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}
		if(!(array_key_exists('uid', $requestData) )){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400')]);
			return $result;
		}
		$fields = [
			'uid' => $requestData['uid']
		];
		$rules = [
			'uid' => 'required',
		];
		$valid = \Validator::make($fields, $rules);
		if ($valid->fails()){
			return [
				'status' => '0',
				'message' => 'validation_failed',
				'errorCode' => '',
				'errorDesc' => $valid->errors()
			];
		}
		if($requestData['isConsultant']==1){
			
			

			if(!empty($requestData['listOfProfession'])){
				$listOfProfession=json_decode($requestData['listOfProfession']);
				$category_json='';
				$categoryid='';
				$category='';
				if($listOfProfession){
					foreach ($listOfProfession as $kp => $valp) { 
						$valp=  (array)$valp;//print_r($valp);exit();
						$categoryid.=$valp['professionId'].',';
						$category.=$valp['professionName'].',';
					} 
					$category=rtrim($category,",");
					$categoryid=rtrim($categoryid,",");
					$category_json=json_encode($listOfProfession);
				}
				
			}
			if(!empty($requestData['scheculeList'])){
				
				$scheculeList=$requestData['scheculeList'];
				$calltime='';
				if($scheculeList){
					$calltime=$scheculeList;
				}
			}

			if(!empty($requestData['listOfSocial'])){
				$sociallist=json_decode($requestData['listOfSocial']);
				$youtube_link='';
				$facebook_link='';
				$twitter_link='';
				$instagram_link='';
				$snapchat_link='';
				$blog_link='';
				$custom_link='';
				if($sociallist){
					foreach($sociallist as $social){
						$social=  (array)$social;
						if($social['name']=='facebook'){
							$facebook_link=$social['link'];	
						}else if($social['name']=='youtube'){
							$youtube_link=$social['link'];	
						}else if($social['name']=='twitter'){
							$twitter_link=$social['link'];	
						}else if($social['name']=='instagram'){
							$instagram_link=$social['link'];	
						}else if($social['name']=='snapchat'){
							$snapchat_link=$social['link'];	
						}else if($social['name']=='blog'){
							$blog_link=$social['link'];	
						}else if($social['name']=='customlink'){
							$custom_link=$social['link'];	
						}
					}
				}
			}
		}

		$data_IMG='';
		if(!empty($requestData['profileImageUrl'])){
			$data_IMG=   $this->imageUpload($requestData['profileImageUrl']);
		}
		
		$matchThese = ['id' => $requestData['uid']];
		$user = CM4UserProfile::where($matchThese)->get(); //print_r($user[0]['cc_password']);exit();
		$status = $user->count();
		if($status!= 0){
			if($data_IMG){
				$profilePic = $data_IMG['name'];
			}else{
				$profilePic = '';
			}
			
			$username = $user[0]['contact_person'];
			$current_rec = CM4UserProfile::find($user[0]['id']);
			$current_rec->user_name = $username;
			if($profilePic!="") {
                $current_rec->profile_pic = $profilePic;
            }
            $current_rec->profile_status = 1;
            $current_rec->update_profile_status = 1;
            $current_rec->isConsultat =$requestData['isConsultant'];
            if($requestData['isConsultant']==1){

            	if(!empty($requestData['alldaytype'])){
	            	$current_rec->age =$requestData['alldaytype'];
	            }

	            if(!empty($requestData['listOfProfession'])){
		            $current_rec->category =$category;
		            $current_rec->category_ids =$categoryid;
		            $current_rec->category_json =$category_json;
	            }

	            if(!empty($requestData['callRate'])){
	            	$current_rec->per_min_val =$requestData['callRate'];
	            }

	            if(!empty($requestData['scheculeList'])){
	           		$current_rec->call_time =$calltime;
	           	}

		            if($requestData['isConsultant']==1){
			            $current_pre = CM4PremiumUser::find($user[0]['id']);
			            if(count($current_pre)>0){ //print_r('f');exit();
			            	$current_pre->user_name = $username;
				            $current_pre->contact_person = $username;
				            if($profilePic!="") {
				                $current_pre->profile_pic = $profilePic;
				            }
				            $current_pre->latitude = $user[0]['latitude'];
				            $current_pre->longitude = $user[0]['longitude'];
				            $current_pre->city = $user[0]['city'];
				            $current_pre->state = $user[0]['state'];
				            $current_pre->country = $user[0]['country'];
				            $current_pre->user_rating = $user[0]['user_rating'];
				            $current_pre->profile_status = 1;

				            if(!empty($requestData['about'])){
				            	$current_pre->about_us = $requestData['about'];
				            }

				            if(!empty($requestData['alldaytype'])){
				           		$current_pre->age = $requestData['alldaytype'];
				           	}
				            $current_pre->update_profile_status = 1;
				            $current_pre->isConsultat =$requestData['isConsultant'];
				            if(!empty($requestData['listOfProfession'])){
					            $current_pre->category =$category;
					            $current_pre->category_ids =$categoryid;
					            $current_pre->category_json =$category_json;
				        	}

				        	if(!empty($requestData['callRate'])){
				            	$current_pre->per_min_val =$requestData['callRate'];
				            }
				            if(!empty($requestData['scheculeList'])){
				            	$current_pre->call_time =$calltime;
				            }

				            if(!empty($requestData['listOfSocial'])){
					            $current_pre->Facebook =$facebook_link;
					            $current_pre->Instagram =$instagram_link;
					            $current_pre->Twitter =$twitter_link;
					            $current_pre->custom_link =$custom_link;
					        }

							$current_pre->save();
							$solrupdate=$this->_update_premium_solr($requestData['uid']);
						}else{ 
							$current_preIN = array();
							$current_preIN['id'] = $user[0]['id'];
				            $current_preIN['user_id'] = $user[0]['user_id'];
				            $current_preIN['user_name'] = $user[0]['user_name'];
				            if($profilePic!="") {
				                $current_preIN['profile_pic'] = $profilePic;
				            }else{
				            	$current_preIN['profile_pic'] = '';
				            }
				            $current_preIN['gender'] = $user[0]['gender'];
				            $current_preIN['locality'] = $user[0]['locality'];
				            $current_preIN['age'] = $requestData['alldaytype'];
				            $current_preIN['address'] = $user[0]['address'];
				            $current_preIN['country'] = $user[0]['country'];
				          	$current_preIN['latitude'] = $user[0]['latitude'];
				            $current_preIN['longitude'] = $user[0]['longitude'];
				            $current_preIN['city'] = $user[0]['city'];
				            $current_preIN['state'] = $user[0]['state'];
							$current_preIN['contact_person'] = $user[0]['contact_person'];
				            $current_preIN['contact_no'] = $user[0]['contact_no'];
				            $current_preIN['verification_status'] = $user[0]['verification_status'];
				            $current_preIN['device_id'] = $user[0]['device_id'];
				            $current_preIN['cc_password'] = $user[0]['cc_password'];
				            $current_preIN['email'] = $user[0]['email'];
				            $current_preIN['cc_fdail'] = $user[0]['cc_fdail'];
				            $current_preIN['data_source'] = $user[0]['data_source'];
				            $current_preIN['live_status'] = $user[0]['live_status'];
				            $current_preIN['updated_at'] = $user[0]['updated_at'];
				            $current_preIN['created_at'] = $user[0]['created_at'];
				            $current_preIN['pincode'] = $user[0]['pincode'];
				            $current_preIN['user_rating'] = $user[0]['user_rating'];
				            $current_preIN['profile_status'] = 1;

				            if(!empty($requestData['about'])){
				            	$current_preIN['about_us'] = $requestData['about'];
				            }else{
				            	$current_preIN['about_us'] = '';
				            }
				            $current_preIN['update_profile_status'] = 1;
				            $current_preIN['isConsultat'] =$requestData['isConsultant'];

				            if(!empty($requestData['listOfProfession'])){
					            $current_preIN['category'] =$category;
					            $current_preIN['category_ids'] =$categoryid;
					            $current_preIN['category_json'] =$category_json;
				        	}else{
				        		$current_preIN['category'] ='';
					            $current_preIN['category_ids'] ='';
					            $current_preIN['category_json'] ='';
				        	}

				        	if(!empty($requestData['callRate'])){
				            	$current_preIN['per_min_val'] =$requestData['callRate'];
				            }

				            if(!empty($requestData['scheculeList'])){
				            	$current_preIN['call_time'] =$calltime;
				            }

				            if(!empty($requestData['listOfSocial'])){
					            $current_preIN['Facebook'] =$facebook_link;
					            $current_preIN['Instagram']=$instagram_link;
					            $current_preIN['Twitter'] =$twitter_link;
					            $current_preIN['custom_link'] =$custom_link;
				        	}
				        	$current_preIN['video_id'] ='';
				        	$current_preIN['video_title'] ='';

				            $current_preIN['callback_date'] =$user[0]['callback_date'];
				            $current_preIN['address_source'] = $user[0]['address_source'];
				            $current_preIN['piggy_bal'] = $user[0]['piggy_bal'];
				            $current_preIN['user_searchid'] = $user[0]['user_searchid'];
				            $current_preIN['telecaller_update'] = $user[0]['telecaller_update'];
				            $current_preIN['comments'] = $user[0]['comments'];
				            $current_preIN['updated_by'] =$user[0]['updated_by'];
				            $current_preIN['is_installed'] =$user[0]['is_installed'];
				            $current_preIN['paid_for'] =$user[0]['paid_for'];
				            $current_preIN['tele_update_date'] =$user[0]['tele_update_date'];
				            $current_preIN['is_callback'] =$user[0]['is_callback'];
				            $current_preIN['c_code'] =$user[0]['c_code']; //print_r($current_preIN);exit();
				            //$inserttime = \DB::connection('callme')->table('cm4_premium_customer')->insertGetId($inserttime);
							$inserttime=CM4PremiumUser::create($current_preIN); 
							$solrupdate=$this->_update_premium_solr($requestData['uid']);
							//print_r($inserttime);exit();
						}
					}

				if(!empty($requestData['listOfSocial'])){
					$matchTheseSocial = ['uid' => $requestData['uid']];
					$usersocial = CM4UserSocial::where($matchTheseSocial)->get(['uid']);
		            $chkstatus = $usersocial->count();
		            if($chkstatus=='0'){
						$socialdata = [
							"uid"=> $requestData['uid'],
							"youtube_link"=>$youtube_link,
							"facebook_link"=>$facebook_link,
							"twitter_link" =>$twitter_link,
							"instagram_link" =>$instagram_link,
							"snapchat_link" =>$snapchat_link,
							"blog_link" =>$blog_link,
							"msg_bf_call" =>'',
							"custom_link"=>$custom_link
						];
						CM4UserSocial::create($socialdata);
		            }else{
						$socialdata = [
							"youtube_link"=>$youtube_link,
							"facebook_link"=>$facebook_link,
							"twitter_link" =>$twitter_link,
							"instagram_link" =>$instagram_link,
							"snapchat_link" =>$snapchat_link,
							"blog_link" =>$blog_link,
							"custom_link"=>$custom_link
							/*//"msg_bf_call" => $msg_bf_call,
							"more_about"=>$requestData['about']*/
						];
						CM4UserSocial::where($matchTheseSocial)->update($socialdata);	
		            }
		        }

		        if(!empty($requestData['about'])){

					$matchTheseSocial = ['uid' => $requestData['uid']];
					$usersocial = CM4UserSocial::where($matchTheseSocial)->get(['uid']);
					$chkstatus = $usersocial->count();
					if($chkstatus=='0'){
						$socialdata = [
							"uid"=> $requestData['uid'],
							"more_about"=>$requestData['about']
						];
						CM4UserSocial::create($socialdata);
					}else{
						$socialdata = [
							"more_about"=>$requestData['about']
						];
						CM4UserSocial::where($matchTheseSocial)->update($socialdata);	
					}
					
		        }
        	}
            if($current_rec->save()){
					$selectqry=\ DB::select("select id as uid,user_id,user_searchid,age,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,cc_fdail,state,call_time,category_json,per_min_val as callRate,age as alldaytype from cm4_user_profile where id=".$requestData['uid']."");

					if($selectqry){		
						if($selectqry[0]->profile_pic){
							$selectqry[0]->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;
						}
						$selectfav=\ DB::select("select id as uid,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state from cm4_user_profile where id in (select favid from cm4_user_favourite where uid = ".$requestData['uid']." and status=1)");
						if($selectfav){
							foreach ($selectfav as $key => $value) {
								$value->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
							}
						}
						$selectqry[0]->bookMarkUser=$selectfav;

						$selectblock=\ DB::select("select id as uid,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state from cm4_user_profile where user_id in (select blocked_to from cm4_block_user where blocked_by = ".$selectqry[0]->user_id." and flag_status=1)");
						if($selectblock){
							foreach ($selectblock as $key => $val) {
								$val->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $val->profile_pic;
							}
						}
						$selectqry[0]->reportNBlock=$selectblock;
						if($requestData['isConsultant']==1){
							$consocial=\ DB::select("select * from cm4_user_social_info where uid =".$selectqry[0]->uid.""); 
							if($consocial){
								$selectqry[0]->about=$consocial[0]->more_about;
								$SocialModel=array('youtube'=>$consocial[0]->youtube_link,'facebook'=>$consocial[0]->facebook_link,'twitter'=>$consocial[0]->twitter_link,'instagram'=>$consocial[0]->instagram_link,'snapchat'=>$consocial[0]->snapchat_link,'customlink'=>$consocial[0]->custom_link);
								$sociallist='';
								foreach ($SocialModel as $key => $value) {
									$sociallist[]=array('name'=>$key,'link'=>$value,'clickCount'=>'');
								}
								$selectqry[0]->SocialModel=$sociallist;
							}else{
								$selectqry[0]->about='';
								$selectqry[0]->SocialModel=array();
							}

							$selectqry[0]->scheculeList=$selectqry[0]->call_time;
							/*$listcat=explode(',', $selectqry[0]->category);
							$catid=explode(',', $selectqry[0]->category_ids);
							$catid_p=explode(',', $selectqry[0]->category_parent_id);
							$category_all=array();
							if($catid[0]!='0'){
								foreach ($catid as $kc => $valct) { //$listcat[$kc];
									$category_all[]=array('professionName'=>$listcat[$kc],'professionId'=>$valct,'professionParentId'=>$catid_p[$kc]);
								}
							}*/
							$selectqry[0]->listOfProfession=json_decode($selectqry[0]->category_json);

							$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$selectqry[0]->uid."' and CURDATE() between offer_start_date and offer_end_date");	
							if(count($selectofferrate)>0){
								$selectqry[0]->offer_rate=$selectofferrate[0]->offer_rate;	
							}else{
								$selectqry[0]->offer_rate='';		
							}

							$is_offer=0;
							$checkdate="SELECT count(*) as num FROM `cm4_user_offers` where  ((CURDATE() between offer_start_date and offer_end_date) or (CURDATE()<=offer_start_date))  and uid='".$selectqry[0]->uid."' and is_active!='2'";
							$qrychkdate= \ DB::select($checkdate);
							if(!empty($qrychkdate)){
								if($qrychkdate[0]->num > 0){
									$is_offer=1;
								}
							}
							$selectqry[0]->is_offer=$is_offer;

							$qry1_tocall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->mobile." and date(starttime ) BETWEEN '".date('Y-m-d')."' and '".date('Y-m-d')."'";
							$datatod= \DB::connection('a2billing')->select($qry1_tocall);
							$todaycall='';
							if($datatod){
								$todaycall=$datatod[0]->calltotal;
							}

							$qry1_totcall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->mobile."";
							$datatot= \DB::connection('a2billing')->select($qry1_totcall);
							$totalcall='';
							if($datatot){
								$totalcall=$datatot[0]->calltotal;
							}
							$selectqry[0]->todaycall=$todaycall;
		        			$selectqry[0]->totalcall=$totalcall;
						}
					}
					$result = collect(["status" => "1", "message" =>'User profile successfully updated','data'=>$selectqry[0]]);
            }

		}else{
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.160')]);
		}
		return response()->json($result, 200);
		//print_r('d');exit();
	}
	//----------end ---------------------------------------------------//

	public function usbankaccount(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}
		if(!(array_key_exists('uid', $requestData) && array_key_exists('accountNumber', $requestData) && array_key_exists('ifscCode', $requestData) && array_key_exists('holderName', $requestData) )){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'), 'errorCode' => '400', 'errorDesc' => \Config::get('constants.results.400')]);
			return $result;
		}
		$fields = [
		'uid' => $requestData['uid']
		];
		$rules = [
		'uid' => 'required',
		];
		$valid = \Validator::make($fields, $rules);
		if ($valid->fails()){
			return [
			'status' => '0',
			'message' => 'validation_failed',
			'errorCode' => '',
			'errorDesc' => $valid->errors()
			];
		}

		$matchThese = ['account_number'=>$requestData['accountNumber'],'routing_number'=> $requestData['ifscCode']];
		$list = UsBankAccount::where($matchThese)->get();
		$status = $list->count(); 
		if($status==0){
			$data_par=array('user_id'=>$requestData['uid'],'account_holder'=>$requestData['holderName'],'account_number'=>$requestData['accountNumber'],'routing_number'=>$requestData['ifscCode']);
			$inserttime=UsBankAccount::create($data_par);
			$filterdata=array('accountId'=>$inserttime->id,'accountNumber'=>$requestData['accountNumber'],'ifscCode'=>$requestData['ifscCode'],'holderName'=>$requestData['holderName']);
			$data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'account'=>$filterdata]);
		}else{
			$data = collect([ "status" => "0","message" =>'Account number already exist']);	
		}
		return response()->json($data, 200);
	}
	//---------------------end ---------------------------------------//

	//-------------------transtion -----------------------------------//
	public function usfetchearnexpenses(){
		$token="";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }

        if (!array_key_exists('uid', $requestData)){
            $result = collect(["status" => ["code" => "400", "message" => \Config::get('constants.results.400')]]);
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
        $matchThese = ['user_id' => $requestData['uid']];
		$other_no="";	
        $userInfo = CM4UserProfile::where($matchThese)->get(['id','marital_status','contact_no']);
        $status = $userInfo->count();
        if($status==0){
            $result = collect(["status" => "1", "message" => \Config::get('constants.results.109'),'data' => array(),'errorCode'=>'109','errorDesc'=>\Config::get('constants.results.109')]);
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

		if($user_id !=0 && $mobilenumber!=''){
        	$qry="SELECT card_id,calledstation,sessiontime,sessionbill,starttime,stoptime,src FROM `cc_call` WHERE (calledstation='".$mobilenumber."' OR src='".$mobilenumber."') and calledstation !=src and sipiax=0 and sessiontime>0 order by id desc ";
        	$data= \DB::connection('a2billing')->select($qry);
        	//-------------------total amount------------------------//
        	$qry1="SELECT count(id) as calltotal,sum(sessiontime) as sessiontime,sum(sessionbill) as sessionbill FROM `cc_call` where calledstation=".$userInfo[0]->contact_no." and date(starttime ) BETWEEN '".date('Y-m-d')."' and '".date('Y-m-d')."'";
        	$data1= \DB::connection('a2billing')->select($qry1);
        	if($data1){
        		$todaycall=$data1[0]->calltotal;
        		if($data1[0]->sessiontime){
        			$todaysessiontime=$data1[0]->sessiontime;
        		}else{
        			$todaysessiontime=array();
        		}
        		if($data1[0]->sessionbill){
        			$todaysessionbill=$data1[0]->sessionbill;
        		}else{
        			$todaysessionbill=array();
        		}
        		
        		
        	}else{
        		$todaycall=array();
        		$todaysessiontime=array();
        		$todaysessionbill=array();
        	}

        	//------------------end ---------------------------------//
        	$callType=array();
			if(count($data)>0){
				foreach($data as $val){ 
					$getsrc =\DB::connection('a2billing')->table('cc_card')
					->where('id', '=', $val->card_id)
					->get();
					$src='';
					if(count($getsrc) > 0){
						$src=$getsrc[0]->phone;
					}
					$val->callType=$this->getcalltype($val->calledstation,$src,$val->sessiontime,$mobilenumber);
					$val->callDuration=	$val->sessiontime;
					if($val->callType=='Incoming Call'){
						$val->callDurationAmt='+ '.preg_replace('/-+/','', $val->sessionbill); 
					}
					if($val->callType=='Outgoing Call'){
						$val->callDurationAmt='- '.preg_replace('/-+/','', $val->sessionbill); 
					}
					$new_date = date('d-M-Y',strtotime($val->starttime));		
					$time = date('h:i A', strtotime($val->starttime));	

					$val->callTime=$time;
					$val->callDate=$new_date;
					$stop_date = date('d-M-Y',strtotime($val->stoptime));		
					$stoptime = date('h:i A', strtotime($val->stoptime));	

					$val->stopcallTime=$stoptime;
					$val->stopcallDate=$stop_date; //print_r($src);
					$usernamemob='';
					if($src==$mobilenumber){
						$usernamemob=$this->getusernamefrommobile($val->calledstation);
					}elseif($val->calledstation==$mobilenumber){
						$usernamemob=$this->getusernamefrommobile($val->src); //userid who called
					}//print_r($usernamemob);exit();

					if($usernamemob!=""){
						$selectqry=\ DB::select("select id,user_id,user_name,user_searchid,contact_person as contactPersonName,profile_pic,isConsultat from cm4_user_profile where user_id='".$usernamemob."'");
					}

					if(!empty($selectqry)){
							$val->contactPersonId=$selectqry[0]->id;
							$val->user_id=$selectqry[0]->user_id;
							if($selectqry[0]->isConsultat==null){
								$isConsultat=0;
							}
							$val->isConsultat=$isConsultat;
							if(trim($selectqry[0]->contactPersonName)=='') {
								$val->contactPersonName =$selectqry[0]->user_name;
							}else{
								$val->contactPersonName=$selectqry[0]->contactPersonName;
							}
							if($selectqry[0]->profile_pic!='') {
								$val->profilePic = \Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;
							}else{
								$val->profilePic = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
							}
							/*if (!empty($selectqry[0]))
								$val->userdata=array($selectqry[0]);			
							else
							$val->userdata=array();	*/
						//}
					}else{
						$val->contactPersonId=array();
						$val->user_id=array();
						$val->contactPersonName =array();
						$val->profilePic = array();
					}
				}
			}
		
        }
       
        if($data){
            $data = collect(["status" => "1","message" => \Config::get('constants.results.100'),'updatedBalance'=>$piggy_bal,"today_call"=>$todaycall,"sessionTime"=>$todaysessiontime,"today_earning"=>$todaysessionbill,"data" => $data]);
        }else{
            $data = collect(["status" => "1",'updatedBalance'=>$piggy_bal,"message" => \Config::get('constants.results.105')]);
            //  return 1;
        }
  		return response()->json($data, 200);
	}
	//--------------------end-----------------------------------------//

	//----------------add money to wallet----------------------------//
	public function usaddmoneytowallet(){
		$token="";
		if (Request::header('content-type') == "application/json") {
            $requestData = Request::json()->all();
        } else {
            $requestData = Request::all();
        }
        if (!(array_key_exists('uid', $requestData) && array_key_exists('mobile',$requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
            return $result;
        }
        $uid=$requestData['uid'];
		$contact_no=$requestData['mobile'];
		$GATEWAYNAME=$requestData['gatewayname'];
		$TXNDATE=$requestData['tandatetime'];
		$CURRENCY=$requestData['currency'];
		$TXNID=$requestData['tanid'];
		$TXNAMOUNT=$requestData['amt'];
		$checkdup=CM4TransactionDetails::where('TXNID',$TXNID)->where('TXNDATE',$TXNDATE)->get(['id']);
		$dupcount=$checkdup->count();
		if($dupcount==0 && $TXNID!=""){
			$data = [
				        "uid" => $uid,
				        "contact_no" =>$contact_no,
						"GATEWAYNAME" => $GATEWAYNAME,
				        "TXNDATE" => $TXNDATE,
				        "CURRENCY" =>$CURRENCY,
						"TXNID" =>$TXNID,
						"TXNAMOUNT" => $TXNAMOUNT
			        ]; 
			    CM4TransactionDetails::create($data);
			 //update to cc_card
			\DB::connection('a2billing')->statement("update cc_card set credit=credit + $TXNAMOUNT where phone='".$contact_no."'");
		}
		$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry); //print_r($CreditInfo);exit();
		$piggybal=0;
		if(count($CreditInfo)=='1'){
			$piggybal=$CreditInfo[0]->piggy_bal;
		}		
		$finaldata=['piggybal'=>$piggybal];
		$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),"updatedBalance"=>$piggybal]);
		return response()->json($result, 200);	
	}
	//-------------------end -----------------------------------------//

	//--------------request money -----------------------------------//
	public function uspaytmAmtReq(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if(!(array_key_exists('mobile', $requestData) && array_key_exists('uid',$requestData) && array_key_exists('amt', $requestData))){
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
			return $result;
		}
		$uid=trim($requestData['uid']);
		$contact_no=trim($requestData['mobile']);
		$reqamt=trim($requestData['amt']);
		$matchThese =['uid'=>trim($requestData['uid'])];
		$qry="SELECT cast(credit as decimal(15,2)) as piggy_bal FROM `cc_card` WHERE phone='".$contact_no."'";
		$CreditInfo= \DB::connection('a2billing')->select($qry);
		$ccpiggybal=$CreditInfo[0]->piggy_bal;
		$current_rec = CM4UserProfile::find(trim($requestData['uid']));
		$piggybal=$current_rec->piggy_bal;
		$updatepiggybal=0;
		if($reqamt<=$ccpiggybal && $reqamt>=50){
			$data = [
			"uid" => $uid,
			"contact_no" =>$contact_no,
			"avail_bal" =>$updatepiggybal,
			"paytm_amt_req" => $reqamt,
			"reference_id"=>''
			];
			$insertpaytmreq=cm4PaytmRequest::create($data);
			if($insertpaytmreq->id){
				$updatepiggybal=$ccpiggybal-$reqamt;	
				$current_rec->piggy_bal=$updatepiggybal;
				$current_rec->save();
				\DB::connection('a2billing')->statement("update cc_card set credit=credit - $reqamt where phone='".$contact_no."'");
				$check_user=['uid' =>$uid];	
		  		$user = CM4PiggyBankAccount::where($check_user)->get(['id']);
		  		if(count($user)>0){
					\ DB::statement("update piggy_bank_ac set total_withdraw=total_withdraw + $reqamt where uid=$uid");	
				}
				//$url="https://www.callme4.com:8443/uploaded_file/notify_pic/Notification_latest.png";
				//$msg=array('page_index'=>5,'message'=>"Hi,$contact_person Paytm Balance will be updated.",'datetime'=>date('Y-m-d H:i:s'),'search_text'=>'','title'=>'Callme4 Request Confirmation.','url'=>$url); 
				//$this->send_notification($contact_no,$msg);
				$user=array('piggy_bank'=>$updatepiggybal);
				$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'updatedBalance'=>$updatepiggybal]);
			}else{
				$result = collect(["status" => "0", "message" => 'Sorry we are not able to process your Request.','errorCode'=>'160','errorDesc'=>'']);	
			}
		}else{
			$result = collect(["status" => "0", "message" => 'Request amount is more than your piggy Balance.','errorCode'=>'160','errorDesc'=>'']);
		}
		return response()->json($result,200);

	}
	//---------------emd---------------------------------------------//
	//---------------transtion histrory------------------------------//
	public function ustrantionhistroy(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('uid', $requestData))) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		}
		$qry1="SELECT created_at as transactionDate,paytm_amt_req as transactionAmt,id as transactionId FROM `cm4_paytm_request` WHERE uid='".$requestData['uid']."'";
		$request1= \ DB::select($qry1);

		$qry="SELECT TXNDATE as transactionDate,TXNAMOUNT as transactionAmt FROM `cm4_paytm_transaction` WHERE uid='".$requestData['uid']."'";
		$request= \ DB::select($qry);

		$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'withdraw'=>$request1, "addmoney" => $request]);
		return response()->json($result,200);
	}
	//---------------end --------------------------------------------//

	public function usviewprofile(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		} else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('uid', $requestData))) {
			$result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400')]);
			return $result;
		}
		$userId=$requestData['uid'];
		$myId=$requestData['myuid'];

		$myselectqry=\ DB::select("select user_id from cm4_user_profile where id=".$myId."");
		if(!empty($myselectqry)){
			$myuserid=$myselectqry[0]->user_id;
		}else{
			$myuserid='';
		}

		if($requestData['isConsultant']==0){
			$selectqry=\ DB::select("select id as uid,user_searchid,user_id,isConsultat,profile_pic,user_name as userName,contact_no,contact_person as fullName,contact_no as mobile,latitude,longitude,city,cc_fdail,state,call_time,category_json,per_min_val as callRate,age as alldaytype from cm4_user_profile where id=".$userId."");
	        if($selectqry){
		        $selectqry[0]->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $selectqry[0]->profile_pic;

		        $selectfav=\ DB::select("select id as uid,user_searchid,user_id,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where id in (select favid from cm4_user_favourite where uid = ".$userId." and status=1)");
		        foreach ($selectfav as $key => $value) {
		        	$value->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $value->profile_pic;
		        }
		        $selectqry[0]->bookMarkUser=$selectfav;

		        $selectblock=\ DB::select("select id as uid,user_id,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where user_id in (select blocked_to from cm4_block_user where blocked_by = ".$selectqry[0]->user_id." and flag_status=1)");
		        foreach ($selectblock as $key => $val) {
		        	$val->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $val->profile_pic;
		        }
		        $qry1_tocall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->contact_no." and date(starttime ) BETWEEN '".date('Y-m-d')."' and '".date('Y-m-d')."'";
		        $datatod= \DB::connection('a2billing')->select($qry1_tocall);
		        $todaycall='';
		        if($datatod){
		        	$todaycall=$datatod[0]->calltotal;
		        }

		        $qry1_totcall="SELECT count(id) as calltotal FROM `cc_call` where calledstation=".$selectqry[0]->contact_no."";
		        $datatot= \DB::connection('a2billing')->select($qry1_totcall);
		        $totalcall='';
		        if($datatot){
		        	$totalcall=$datatot[0]->calltotal;
		        }
				$isBlocked=\ DB::select("select blocked_to from cm4_block_user where blocked_by = ".$myuserid." and blocked_to = ".$selectqry[0]->user_id." and flag_status=1");
				if($isBlocked){
					$selectqry[0]->isBlocked='true';
				}else{
					$selectqry[0]->isBlocked='false';
				}

				$isBookmarked=\ DB::select("select favid from cm4_user_favourite where uid = ".$requestData['myuid']." and favid=".$userId." and status=1");
				if($isBookmarked){
					$selectqry[0]->isBookmarked='true';
				}else{
					$selectqry[0]->isBookmarked='false';
				}	

		        $selectqry[0]->todaycall=$todaycall;
		        $selectqry[0]->totalcall=$totalcall;
		        $selectqry[0]->reportNBlock=$selectblock;
		        $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'data'=>$selectqry[0]]);
		    }else{
		    	$result = collect(["status" => "0", "message" => \Config::get('constants.results.160')]);
		    }
		}else{
			$selectcons=\ DB::select("select id as uid,user_searchid,user_id,isConsultat,category_json,per_min_val as callRate,call_time,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,age as alldaytype,cc_fdail from cm4_user_profile where id=".$userId." ");
			if($selectcons){
				$constants=array();
		        /*foreach ($selectcons as $key => $cons) { */
					$selectcons[0]->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $selectcons[0]->profile_pic;
					$conselectfav=\ DB::select("select id as uid,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where id in (select favid from cm4_user_favourite where uid = ".$selectcons[0]->uid." and status=1)");
					foreach ($conselectfav as $key => $cf) {
					$cf->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $cf->profile_pic;
					}
					$selectcons[0]->bookMarkUser=$conselectfav;

					$conselectblock=\ DB::select("select id as uid,user_id,user_searchid,isConsultat,profile_pic,user_name as userName,contact_person as fullName,contact_no as mobile,latitude,longitude,city,state,cc_fdail from cm4_user_profile where user_id in (select blocked_to from cm4_block_user where blocked_by = ".$selectcons[0]->user_id." and flag_status=1)");
					foreach ($conselectblock as $key => $cval) { 
						$cval->profileImageUrl=\Config::get('constants.results.root')."/user_pic/" . $cval->profile_pic;
					}
					$selectcons[0]->reportNBlock=$conselectblock;
					$listOfProfession=json_decode($selectcons[0]->category_json);
					if(!empty($listOfProfession)){
						$selectcons[0]->listOfProfession=$listOfProfession;
					}else{
						$selectcons[0]->listOfProfession=array();
					}

					$consocial=\ DB::select("select * from cm4_user_social_info where uid =".$selectcons[0]->uid.""); 
					if($consocial){
						$selectcons[0]->about=$consocial[0]->more_about;
						$SocialModel=array('youtube'=>$consocial[0]->youtube_link,'facebook'=>$consocial[0]->facebook_link,'twitter'=>$consocial[0]->twitter_link,'instagram'=>$consocial[0]->instagram_link,'snapchat'=>$consocial[0]->snapchat_link,'customlink'=>$consocial[0]->custom_link);
						$sociallist='';
						foreach ($SocialModel as $key => $value) {
							$sociallist[]=array('name'=>$key,'link'=>$value,'clickCount'=>'');
						}
						$selectcons[0]->SocialModel=$sociallist;
					}else{
						$selectcons[0]->about='';
						$selectcons[0]->SocialModel=array();
					}
					//$scheculeList=explode(',', $cons->call_time);
					if($selectcons[0]->call_time){
						$sclist=$selectcons[0]->call_time;
					}else{
						$sclist='';
					}
					
					$selectcons[0]->scheculeList=$sclist;
					$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$selectcons[0]->uid."'";
					$raterevqryex= \ DB::select($rate_rev_qry);
					if(count($raterevqryex)>0)
					{
						//$value->reviewcount=$raterevqryex[0]->reviewcount;	
						$selectcons[0]->avgrating=$raterevqryex[0]->avgrating;
					}
					else
					{
						if(count($raterevqryex)>0)
						{
							//$value->reviewcount='0';	
							$selectcons[0]->avgrating='0';
						}	
					}
					$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$selectcons[0]->uid."' and CURDATE() between offer_start_date and offer_end_date");	
				    if(count($selectofferrate)>0)
					{
						$selectcons[0]->offer_rate=$selectofferrate[0]->offer_rate;	
					}
					else
					{
						$selectcons[0]->offer_rate='';		
					}
					$selectcons[0]->videoId='';
					$selectcons[0]->videoTitle='';
					$selectcons[0]->onlineStatus='';	
					$isBlocked=\ DB::select("select blocked_to from cm4_block_user where blocked_by = ".$myuserid." and blocked_to = ".$selectcons[0]->uid." and flag_status=1");
					if($isBlocked){
						$selectcons[0]->isBlocked='true';
					}else{
						$selectcons[0]->isBlocked='false';
					}

					$isBookmarked=\ DB::select("select favid from cm4_user_favourite where uid = ".$requestData['myuid']." and favid=".$selectcons[0]->uid." and status=1");
					if($isBookmarked){
						$selectcons[0]->isBookmarked='true';
					}else{
						$selectcons[0]->isBookmarked='false';
					}		
					
		        /*}*/
		        $result = collect(["status" => "1", "message" => \Config::get('constants.results.111'),'data'=>$selectcons[0]]);
			}else{
				$result = collect(["status" => "0", "message" => \Config::get('constants.results.160')]);
			}
		}
	    return response()->json($result, 200);
	    
	}

	//---------------end --------------------------------------------//


	//------------------call api -----------------------------------------//

	public function uscm4callingapi(){
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else {
			$requestData = Request::all();
		}
		if (!(array_key_exists('profile_username', $requestData) && array_key_exists('f_dail', $requestData) && array_key_exists('called_username', $requestData))) {
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

		if ($requestData['profile_username']== $requestData['called_username']) {
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
		$data =CM4UserProfile::where('user_id', '=', $requestData['called_username'])->get(['cc_fdail','cc_password','id','contact_no','email','user_name','per_min_val','c_code']);
		$cid= $requestData['profile_username'];
		if(count($data)>0){
			$calleduserchares=$data[0]->per_min_val;
			$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$data[0]->id."' and (CURDATE() between offer_start_date and offer_end_date)");
			if(count($selectofferrate)>0){
				$calleduserchares=$selectofferrate[0]->offer_rate;	
			}
			if($calleduserchares>0){
				$qry="SELECT cast(credit as decimal(6,2)) as piggy_bal FROM `cc_card` WHERE username=$requestData[profile_username]";
				$CreditInfo= \DB::connection('a2billing')->select($qry);
				$ccpiggybal=$CreditInfo[0]->piggy_bal;
				if($ccpiggybal<$calleduserchares){
					$result = collect(["status" => "2", "message" => 'Do not have sufficient Balance.','errorCode'=>'104','errorDesc'=>'Do not have sufficient Balance.', "device_key" => $token,"piggy_bal"=>$ccpiggybal]);
					return $result;
				}else{
					if($calleduserchares=='1.00' || $calleduserchares=='1'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'5'));
					}
					if($calleduserchares=='2.00' || $calleduserchares=='2'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'6'));
					}
					if($calleduserchares=='3.00' || $calleduserchares=='3'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'7'));
					}
					if($calleduserchares=='4.00' || $calleduserchares=='4'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'8'));
					}
					if($calleduserchares=='5.00' || $calleduserchares=='5'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'9'));
					}
					if($calleduserchares=='6.00' || $calleduserchares=='6'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'10'));
					}
					if($calleduserchares=='7.00' || $calleduserchares=='7'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'11'));
					}
					if($calleduserchares=='8.00' || $calleduserchares=='8'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'12'));
					}
					if($calleduserchares=='9.00' || $calleduserchares=='9'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'13'));
					}
					if($calleduserchares=='10.00' || $calleduserchares=='10'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'14'));
					}
					if($calleduserchares=='15.00' || $calleduserchares=='15'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'21'));
					}
					if($calleduserchares=='20.00' || $calleduserchares=='20'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'16'));
					}
					if($calleduserchares=='25.00' || $calleduserchares=='25'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'22'));
					}
					if($calleduserchares=='30.00' || $calleduserchares=='30'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'17'));
					}
					if($calleduserchares=='35.00' || $calleduserchares=='35'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'23'));
					}
					if($calleduserchares=='40.00' || $calleduserchares=='40'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'18'));
					}
					if($calleduserchares=='50.00' || $calleduserchares=='50'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'19'));
					}
					if($calleduserchares=='60.00' || $calleduserchares=='60'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'26'));
					}
					if($calleduserchares=='70.00' || $calleduserchares=='70'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'27'));
					}
					if($calleduserchares=='80.00' || $calleduserchares=='80'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'28'));
					}
					if($calleduserchares=='90.00' || $calleduserchares=='90'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'29'));
					}
					if($calleduserchares=='100.00' || $calleduserchares=='100'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'25'));
					}
					if($calleduserchares=='150.00' || $calleduserchares=='150'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'20'));
					}
					if($calleduserchares=='200.00' || $calleduserchares=='200'){
						$callerusername=$requestData['profile_username'];
						$updatecallplan = \DB::connection('a2billing')->table('cc_card')->where('username', '=',$callerusername)->update(array('tariff' =>'30'));
					}

				}
			}else{
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
			if(count($data)==0){	
				$registertocccard=$this->register_to_cccard($requestData['called_username'],$dailNo,$email,$fid,$cc_password,$user_name);
			}
			$data =CM4UserProfile::where('user_id', '=', $requestData['profile_username'])->get(['cc_fdail','id', 'contact_no']);
			if(count($data)==0){
                $result = collect(["status" => "0", "message" => \Config::get('constants.results.104'),'errorCode'=>'104','errorDesc'=>\Config::get('constants.results.104'), "device_key" => $token]);
                return $result;
            }
			$sndr_fid=$data[0]->cc_fdail;
			$sndr_dailNo=$data[0]->contact_no;
			$data = [
				'sndr_fid' =>$sndr_fid,
				'rcvr_uid' => $rcvr_uid
			];
			$status2 = \DB::connection('a2billing')->table('cc_rcvd_pstn_call')->insert($data);
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
				}else{
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
				$cc_dial_detail_array = [
					'cid' =>$cid,
					'fid' => $fid,
					'phone' => $dailNo,
					'caller_phone' =>$sndr_dailNo,
					'ext' => $passdialnum
				];
				$status = \DB::connection('a2billing')->table('cc_dial_detail')->insertGetId($cc_dial_detail_array);
				$data = [
					'username' =>$cid,
					'fid' => $fid,
					'phone1' => $sndr_dailNo,
					'phone2' =>$dailNo,
					'passdialnum' => $passdialnum,
					'isphone2active'=>$phone2active
				];
				$status2 = \DB::connection('a2billing')->table('cc_dial_detail_pdn')->insert($data);
				$query="select id from CM4_user_info where  phone='".$dailNo."'";
				$isphone2active= \ DB::select($query);	
				if(count($isphone2active)>0){
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

	//------------------end ----------------------------------------------//

	//----------------search api------------------------------------------//
	public function ussearchnewapi(){ 
		$token="";
		if (Request::header('content-type') == "application/json") {
			$requestData = Request::json()->all();
		}else{
			$requestData = Request::all();
		}
		if(!(array_key_exists('text', $requestData) && array_key_exists('uid', $requestData) && array_key_exists('start', $requestData) && array_key_exists('rows', $requestData))) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
            return $result;
        }
        if (count($requestData) < 4) {
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.400'),'errorCode'=>'400','errorDesc'=>\Config::get('constants.results.400'), "device_key" => $token]);
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
			$popular_youtube_ids="";
			$popular_youtube_ids_or="";
			$ssc_ids="";
			$board_ids="";
			if($text=='Popular+Youtubers' or $text=='SSC+Exam+Preparation' or $text=='Board+Exam+Preparation' or $text=='Astrology' or $text=='Relationship'){ 
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
			}else{
				$premium_url="http://172.16.200.35:8983/solr/premium_search/select?q=$text(*)&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=tags^30.0+category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";	
			}
			$premium_url = preg_replace('!\s+!', '+', $premium_url);
			$premium_response    = file_get_contents($premium_url);
			$premium_response = json_decode($premium_response,true);
			$premium_response_arr=  $premium_response["response"]["docs"]; 
			if(count($premium_response_arr)>0){
				$count=0;
				foreach($premium_response_arr as $val){
					if($count==0){
						$blogger_ids.="-id:$val[id]";
					}else{
						$blogger_ids.=" AND -id:$val[id]";
					}
					$filterids=urlencode($blogger_ids);
					$val['user_name']=trim($val['user_name']);
					$val['cc_fdail']=$val['cc_fdail'];
					$val['user_id']=$val['user_id'];
					$val['contact_no']=$val['contact_no'];
					if(!(array_key_exists('live_status', $val))){
						$val['live_status']=0;	
					}
					if($val['live_status']==1 && $val['contact_person']!=" "){
						$val['user_name']=$val['contact_person'];	
					}
					if(isset($val['contact_person'])) {
						$val['contact_person'] = $val['contact_person'];
					}else{
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
					}else if($val['service']!=""){
						if(preg_match("/;/",$val['service'])){
							$category=explode(';',$val['service']);
							foreach($category as $getcategory){
								$newsearchtext=explode(':',$getcategory);
								$searchtext[]=$newsearchtext[0];
							}
						}else{
							$category=explode(':',$val['service']);
							$searchtext[]=$category[0];
						}
						$categorytext=implode(",",$searchtext);
						unset($searchtext);
						$val['tags']=$categorytext;
					}else{
						$val['tags']="";
					}
					$matchThese=['uid'=> $userId,'favid'=>$val['id'],'status'=>1];
					$user = CM4UserFavourite::where($matchThese)->get();
					$val['favourite_status']=  $user->count()>0?1:0;
					$searched_uid=$val['id'];
					$searched_contact=$val['contact_no'];	
					$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
					$raterevqryex= \ DB::select($rate_rev_qry);
					if(count($raterevqryex)>0){
						$val['reviewcount']=$raterevqryex[0]->reviewcount;	
						$val['avgrating']=$raterevqryex[0]->avgrating;
					}
					$today_date=date('Y-m-d');
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
					$select_video="SELECT video_id,video_title,per_min_val,online_status,Is_youtube,is_verified from cm4_premium_customer where id='".$searched_uid."'";
					$getvideo= \ DB::select($select_video);
					if(count($getvideo)>0){
						if($getvideo[0]->video_id!=''){
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
					}else{
						$val['thumbnail_big']="";
						$val['video_id']="";
						$val['is_youtube']="0";	
						$val['video_title']="";
						$val['is_verified']=0;
					}
					$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
					if(count($selectofferrate)>0){
						$val['offer_rate']=$selectofferrate[0]->offer_rate;	
					}else{
						$val['offer_rate']='';		
					}
					$select_profile_pic="SELECT profile_pic from cm4_user_profile where id='".$searched_uid."' and is_installed='1'";
					$getimage= \ DB::select($select_profile_pic);
					if(count($getimage)>0){
						if($getimage[0]->profile_pic!='') {
							$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/" . $getimage[0]->profile_pic;
						}else{
							$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;
						}
					}else{
						$val['profile_pic'] = \Config::get('constants.results.root')."/user_pic/noImage.png" ;	
					}
					array_push($records,$val);
					array_push($tags, $val['tags']);
					$count ++;	
				}
			}
			$searchuser_id="";
			if($blogger_ids!=""){
				$searchuser_id=	"&fq=($blogger_ids)";
			}	
			$distance=500;	
			$details_url = "http://172.16.200.35:8983/solr/search/select?q=$text(*)$searchuser_id&start=$start&rows=$rows&fl=id,contact_person,profile_pic,cc_fdail,user_id,live_status,user_name,contact_no,latitude,longitude,address,call_time,user_searchid,locality,service,tags:tags&defType=edismax&qf=category^20.0+user_name^0.3+contact_person^10.0+user_searchid^8.0&wt=json&indent=true&spatial=true&sort=live_status desc";
		}
		$details_url = preg_replace('!\s+!', '+', $details_url);
		$response    = file_get_contents($details_url);
       	$response = json_decode($response, true);
       	$response["responseHeader"]["params"]["fq"]="CallMe4";
		$response_arr= $response["response"]["docs"];
		foreach($response_arr as $val){
			$val['user_name']=trim($val['user_name']);
            $val['cc_fdail']=$val['cc_fdail'];
            $val['user_id']=$val['user_id'];
            $val['contact_no']=$val['contact_no'];
            if(!(array_key_exists('live_status', $val))){
				$val['live_status']=0;	
			}

			if($val['live_status']==1 && $val['contact_person']!=" "){
				$val['user_name']=$val['contact_person'];	
			}
			if(isset($val['contact_person'])) {
                $val['contact_person'] = $val['contact_person'];
            }else{
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
			$searched_uid=$val['id'];
			$searched_contact=$val['contact_no'];	
			$rate_rev_qry="SELECT count(*) as reviewcount,ifnull(avg(rating),0) avgrating FROM `cm4_rating_review` WHERE rating >0 and `given_to_uid`='".$searched_uid."'";
			$raterevqryex= \ DB::select($rate_rev_qry);
			if(count($raterevqryex)>0){
				$val['reviewcount']=$raterevqryex[0]->reviewcount;	
				$val['avgrating']=$raterevqryex[0]->avgrating;
			}
			$today_date=date('Y-m-d');
			$getforce_timeset="SELECT uid,online_status as online_updated  FROM create_rate_time WHERE uid = '".$searched_uid."' and date(created_at)='".$today_date."' and online_status='0'";
			$force_ex= \ DB::select($getforce_timeset);
			if(count($force_ex)>0){
				$val['force_close']='1';	
			}else{
				$val['force_close']='0';	
			}
			$querystatus="SELECT per_min_val,is_callback as online_status from cm4_user_profile where id='".$searched_uid."'";
			$status_query_ex= \ DB::select($querystatus);
			if(count($status_query_ex)>0){
				$val['per_min_val']=$status_query_ex[0]->per_min_val;
				$val['online_status']=$status_query_ex[0]->online_status;
			}else{
				$val['per_min_val']="0";
				$val['online_status']="1";	
			}
			$val['is_premium']='0';
			$val['is_youtube']="0";
			$select_video="SELECT video_id,video_title,Is_youtube,is_verified from cm4_premium_customer where id='".$searched_uid."'";
			$getvideo= \ DB::select($select_video);
			if(count($getvideo)>0){
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
				$val['is_youtube']=$getvideo[0]->Is_youtube;
				$val['is_verified']=$getvideo[0]->is_verified;
			}else{
				$val['thumbnail_big']="";
				$val['video_id']="";	
				$val['video_title']="";
				$val['is_verified']=0;
			}
			$selectofferrate=\ DB::select("SELECT offer_rate FROM `cm4_user_offers` WHERE is_active='1' and uid='".$searched_uid."'");	
		    if(count($selectofferrate)>0){
				$val['offer_rate']=$selectofferrate[0]->offer_rate;	
			}else{
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
	    $data = array("text" => $text,"uid" => $userId,"record_count"=>$response["response"]["numFound"]);
	    if($total_record!=0) {
	    	$result = collect(["status" => "1", "message" => \Config::get('constants.results.100'),'errorCode'=>'100','errorDesc'=>\Config::get('constants.results.100'),'data'=>$response, "device_key" => $token]);
	    }else{
            $result = collect(["status" => "0", "message" => \Config::get('constants.results.105'),'errorCode'=>'','errorDesc'=>'', "device_key" => $token]);
        }
        return response()->json($result, 200);

	}

	//------------------end ----------------------------------------------//
	public function sendsms($phoneNumber , $txt){
		//$ch = curl_init();
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

	//----------------rand string---------------------------//

	public function rand_string($length){
		$chars = "0123456789";
		$str="";
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}
		return $str;
    }

    //------------------get code-------------------//
	public function gen_referal_code() {
		$ctr=0;
		$card_gen = "";
		$flag = true;
		while ($flag) {
			$ctr++;
			$card_gen = $this->MDP_STRING(8);
			$data =CM4UserRefer::where('refer_code', '=', $card_gen)->get(['refer_code']);
			if(count($data) > 0)
			continue;
			if ($ctr == 1000)
			return false;
			$flag = false;
		}
		return ($card_gen) ? $card_gen : false;
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

	public function register($phone,$username,$email,$country_code){
		$genratedVal = $this->gen_card_with_alias();
        $activation = $this->gen_activation_code();
        $phoneNumber = $phone;
        $omobile = '00'.$country_code . $phoneNumber;
        $fdial = $country_code . $this->convertFdialkey($omobile);
        $userId=$genratedVal['user'];
        $useralias=$genratedVal['useralias'];
        $pass=$genratedVal['pass'];
        $loginkey=$genratedVal['loginkey'];
        $mobile=$country_code.$phone;
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
			$data = \DB::connection('a2billing')->table('cc_card')
            ->where('phone', '=', $mobile)
            ->get();
			$id = $data[0]->id;
			 
		}else{
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

	public function gen_card_with_alias($table = "cc_card") {
		$ctr=0;
		$flag = true;
		while ($flag) {
			$ctr++;
			$card_gen = $this->MDP_NUMERIC(10);
			$alias_gen = $this->MDP_NUMERIC(15);
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
	public function MDP_NUMERIC($chrs = 10) {
        $myrand = "";
        for ($i = 0; $i < $chrs; $i++) {
            $myrand .= mt_rand(0, 9);
        }
        return $myrand;
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

	public function imageUpload($data) {
		//$destinationPath = 'uploads/' ;
		$destinationPath = $_SERVER['DOCUMENT_ROOT']."/uploaded_file/user_pic" ;
		if(!is_dir($destinationPath)){
			mkdir($destinationPath, 0777, true);
		}
		$filename = time().'.jpg';
		$status= file_put_contents($destinationPath . '/' . $filename, base64_decode($data));
		$data =['status'=>$status,'name'=>$filename];
		return $data;
	}

	public function multi_implode($array, $glue) {
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

	public function getusernamefrommobile($mobilenumber){ 
		$getsrc =\DB::connection('a2billing')->table('cc_card')
		->where('phone', '=', $mobilenumber)
		->get(['username']);
		if(count($getsrc)>0){
			$usernamemob=$getsrc[0]->username;
			return $usernamemob;
		}

	}

	public function today_timing($dayandtime){
		$getdaytime = explode(',',$dayandtime);
		if(count($getdaytime)>1){	
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
			$key = array_search($weekday,$output_array);
			return $output_array[$key + 1];
		}else{
		 	return $dayandtime;
		}
    }


	public function _update_premium_solr($user_id){
		$tags="";
		$sql1="select id,category_ids,cc_fdail,user_id,user_name,contact_person,contact_no,profile_pic,category,latitude,longitude,address,locality,call_time,user_searchid from cm4_premium_customer where id='$user_id'";
		$userdata= \ DB::select($sql1);
		if(count($userdata)>0){
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
			if($category_ids==""){
				$category_ids=0;	
			}
			$geolocation=$latitude.",".$longitude;
			$qry="SELECT group_concat(category_name) AS tags FROM `cm4_categories` WHERE`cm4_categories`.`category_id` IN ($category_ids) and `cm4_categories`.`type_id`=1";
			$gettags= \DB::select($qry);
			if(count($gettags)>0){
				$tags=$gettags[0]->tags;
			}	
			if($tags==""){
				$tags="Others";	
			}
			if($contact_person==""){
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
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			$output = json_decode(curl_exec($ch));
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($responseCode == 200){
				return true;
			}else{
				return false;
			}
		}
	}

}
    