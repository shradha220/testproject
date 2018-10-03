<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
 header("Access-Control-Allow-Origin: *");
 header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, If-Modified-Since, Cache-Control, Pragma");

App::singleton('oauth2', function() {
  
/* 
 * We recommend explictly configuring a connection timeout (see tips & tricks
 * below). Specify the replica set name to avoid connection errors.
 */

	$dsn      = 'mysql:dbname=callme;host=localhost';
	$username = 'root';
	$password = '';
    $db =array("host"=>"localhost","port"=>27017,"database"=>"callme","username"=>"root","password"=>"");
	$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

	
	$server = new OAuth2\Server($storage);
	
	$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
	$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
	$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage));
	
	return $server;
});

Route::post('oauth/token', function()
{
  $bridgedRequest  = \OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
	$bridgedResponse = new \OAuth2\HttpFoundationBridge\Response();
	
	$bridgedResponse = App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);
	
	return $bridgedResponse;
});


Route::post('oauth/refreshtoken', function()
{
  $bridgedRequest  = \OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
	$bridgedResponse = new \OAuth2\HttpFoundationBridge\Response();
	
	$bridgedResponse = App::make('oauth2')->handleTokenRequest($bridgedRequest, $bridgedResponse);
	
	return $bridgedResponse;
});


//, 'middleware' => ['permission','oauth']
//, 'middleware' => 'permission'
//die('hello');
   Route::get('/test' ,['uses' => 'CallMeController@index', 'as' => 'callme.index' ] );
//, 'middleware' => 'permission'
Route::post('/UpdateBasicDetails',['uses'=>'CallMeController@Update_User_basic_Details', 'as' => 'callme.Update_User_basic_Details']);

Route::post('/test2' ,['uses' => 'CallMeController@getCategory', 'as' => 'callme.getCategory' ] );
Route::post('/applyforads',['uses'=>'CallMeController@Apply_For_Ads', 'as' => 'callme.Apply_For_Ads']);
Route::post('/fetchfacebookdetails' ,['uses' => 'CallMeController@getfacebookdetails', 'as' => 'callme.getfacebookdetails' ]);

Route::post('/category' ,['uses' => 'CallMeController@solrCategory', 'as' => 'callme.solrCategory' ] );
Route::post('/solrcategory' ,['uses' => 'CallMeController@solrCategory', 'as' => 'callme.solrCategory' ] );
Route::post('/solrcategorybypid' ,['uses' => 'CallMeController@get_categorybypid', 'as' => 'callme.get_categorybypid' ]);
Route::post('/solrtestcategorybypid' ,['uses' => 'CallMeController@get_testcategorybypid', 'as' => 'callme.get_testcategorybypid' ]);
Route::post('/getsearchservice' ,['uses' => 'CallMeController@getsearchservice', 'as' => 'callme.getsearchservice']);
Route::post('/getsearchservicenew' ,['uses' => 'CallMeController@getsearchservicenew', 'as' => 'callme.getsearchservicenew']);

//New API 
Route::post('/verifiedAddedPeopleList', ['uses' => 'CallMeController@verifiedAddedPeople', 'as' => 'callme.verifiedAddedPeople' ]);
Route::post('/register', ['uses' => 'CallMeController@register', 'as' => 'callme.register' ]);
Route::post('/getotpcode', ['uses' => 'CallMeController@getcode', 'as' => 'callme.getcode' ]);
Route::post('/updateinfo', ['uses' => 'CallMeController@updateUserInfo', 'as' => 'callme.updateUserInfo' ]);
 /*This is new function for update profile with user per min value.*/

Route::post('/updateprofilebyuser', ['uses' => 'CallMeController@updateUserProfile', 'as' => 'callme.updateUserProfile']);
Route::post('/updateprofilebyuser_new', ['uses' => 'CallMeController@updateUserProfile_new', 'as' => 'callme.updateUserProfile_new']);
Route::post('/updateprofilebyuserios', ['uses' => 'CallMeController@updateUserProfile_ios', 'as' => 'callme.updateUserProfile_ios']);

Route::post('/updatedeviceid', ['uses' => 'CallMeController@updateDeviceId', 'as' => 'callme.updateDeviceId' ]);
Route::post('/updateusername', ['uses' => 'CallMeController@updateName', 'as' => 'callme.updateName' ]);
Route::post('/verifysmscode', ['uses' => 'CallMeController@verifySms', 'as' => 'callme.verifySms' ]);
Route::post('/verifysmscode_new', ['uses' => 'CallMeController@verifySms_new', 'as' => 'callme.verifySms_new' ]);
Route::post('/verifysmscode1', ['uses' => 'CallMeController@verifySms1', 'as' => 'callme.verifySms1' ]);

Route::post('/verifysmsnew', ['uses' => 'CallMeController@verifySmsnew', 'as' => 'callme.verifySmsnew']);

Route::post('/cm4search', ['uses' => 'CallMeController@search', 'as' => 'callme.search' ]);
Route::post('/searchnew', ['uses' => 'CallMeController@searchnew', 'as' => 'callme.searchnew' ]);

Route::post('/searchnew_api', ['uses' => 'CallMeController@searchnewapi', 'as' => 'callme.searchnewapi' ]);
//Search IOS
Route::post('/searchios', ['uses' => 'CallMeController@searchios', 'as' => 'callme.searchios' ]);


//Route::post('/call', ['uses' => 'CallMeController@toCall', 'as' => 'callme.toCall' ]);
Route::post('/userinfo', ['uses' => 'CallMeController@getUserInfo', 'as' => 'callme.getUserInfo' ]);
//Route::post('/call', ['uses' => 'CallMeController@toCallwithoutccdail', 'as' => 'callme.toCallwithoutccdail' ]);
Route::post('/call', ['uses' => 'CallMeController@freecallapi', 'as' => 'callme.freecallapi' ]);

Route::post('/addlistother', ['uses' => 'CallMeController@addPeople', 'as' => 'callme.addPeople' ]);
Route::post('/boosterdata', ['uses' => 'CallMeController@addPeopleBooster', 'as' => 'callme.addPeopleBooster' ]);
Route::post('/fetchboosterdata', ['uses' => 'CallMeController@userBoosterPeopleDetails', 'as' => 'callme.userBoosterPeopleDetails' ]);
Route::post('/addaccount', ['uses' => 'CallMeController@addAcount', 'as' => 'callme.addAcount' ]);
Route::post('/removeaccount', ['uses' => 'CallMeController@removeAcount', 'as' => 'callme.removeAcount' ]);
Route::post('/fetchaccount', ['uses' => 'CallMeController@userAccountInfo', 'as' => 'callme.userAccountInfo' ]);
Route::post('/withdraw', ['uses' => 'CallMeController@withdrawRequest', 'as' => 'callme.withdrawRequest' ]);

Route::post('/addbooster', ['uses' => 'CallMeController@userBoosterPack', 'as' => 'callme.userBoosterPack' ]);
Route::post('/boosterlist', ['uses' => 'CallMeController@boosterPacks', 'as' => 'callme.boosterPacks' ]);
Route::post('/selectedbooster', ['uses' => 'CallMeController@selectedboosterPacks', 'as' => 'callme.selectedboosterPacks' ]);
Route::post('/verifiedList', ['uses' => 'CallMeController@verifiedPeople', 'as' => 'callme.verifiedPeople' ]);
Route::post('/markfavourite', ['uses' => 'CallMeController@markFavourite', 'as' => 'callme.markFavourite' ]);
Route::post('/fetchfavouritelist', ['uses' => 'CallMeController@fetchFavourite', 'as' => 'callme.fetchFavourite' ]);
Route::post('/getcallhistory', ['uses' => 'CallMeController@callHistory', 'as' => 'callme.callHistory' ]);
//FETCH EARN EXPENSES
//Route::post('/fetch_earn_expenses', ['uses' => 'CallMeController@fetch_earn_expenses_new', 'as' => 'callme.fetch_earn_expenses_new' ]);
Route::post('/fetch_earn_expenses', ['uses' => 'CallMeController@fetch_earn_expenses_ctry_code', 'as' => 'callme.fetch_earn_expenses_ctry_code' ]);

Route::post('/fetch_earn_expenses_new', ['uses' => 'CallMeController@fetch_earn_expenses_new', 'as' => 'callme.fetch_earn_expenses_new' ]);

Route::post('/fetch_earn_expenses_latest', ['uses' => 'CallMeController@fetch_earn_expenses_latest', 'as' => 'callme.fetch_earn_expenses_latest' ]);


Route::post('/userinfomobile', ['uses' => 'CallMeController@userinfoMobile', 'as' => 'callme.userinfoMobile' ]);
Route::post('/fetchusername', ['uses' => 'CallMeController@fetchusername', 'as' => 'callme.fetchusername' ]);

Route::post('/getpiggybalance', ['uses' => 'CallMeController@getpiggybalance', 'as' => 'callme.getpiggybalance' ]);

Route::post('/useralivestatus', ['uses' => 'CallMeController@useralivestatus', 'as' => 'callme.useralivestatus1' ]);
Route::post('/getupdateuserdeviceid', ['uses' => 'CallMeController@updateuserdeviceid', 'as' => 'callme.updateuserdeviceid']);
Route::post('/freecallapi', ['uses' => 'CallMeController@freecallapi', 'as' => 'callme.freecallapi']);
//New Calling api updated on 10/01/2017 for direct calling 
//Route::post('/cm4callingapi', ['uses' => 'CallMeController@cm4callapi', 'as' => 'callme.cm4callapi']);
Route::post('/cm4callingapi', ['uses' => 'CallMeController@cm4callapi_ctry_code', 'as' => 'callme.cm4callapi_ctry_code']);
Route::post('/cm4callapitest', ['uses' => 'CallMeController@cm4callapi_ctry_code', 'as' => 'callme.cm4callapi_ctry_code']);

//Update Piggy Bank Balance
Route::post('/addpaytmtransaction', ['uses' => 'CallMeController@addpaytmtransaction', 'as' => 'callme.cm4callapi']);

//For Paytm Request added on 19/01/2017
Route::post('/userAmtReq',['uses' => 'CallMeController@paytmAmtReq', 'as' => 'callme.paytmAmtReq']);
Route::post('/paytmBloggerReq',['uses' => 'CallMeController@paytmBloggerReq', 'as' => 'callme.paytmBloggerReq']);

Route::post('/addratingreview', ['uses' => 'CallMeController@addRatingReview', 'as' => 'callme.addRatingReview']);
Route::post('/outgoingcallstatus',['uses' => 'CallMeController@outgoingcallstatus', 'as' => 'callme.outgoingcallstatus']);
Route::post('/syncusercontact', ['uses' => 'CallMeController@syncusercontact', 'as' => 'callme.syncusercontact']);
Route::post('/MyNetworkContactList', ['uses' => 'CallMeController@MyNetwork', 'as' => 'callme.MyNetwork']);

Route::post('/MyNetworkContactList2', ['uses' => 'CallMeController@MyNetwork2', 'as' => 'callme.MyNetwork2']);

Route::post('/MyNetworkContactList3', ['uses' => 'CallMeController@MyNetwork3', 'as' => 'callme.MyNetwork3']);


Route::post('/invitefromphonebook', ['uses' => 'CallMeController@invitefromphonebook', 'as' => 'callme.invitefromphonebook']);
Route::post('/getuserprofilebyid', ['uses' => 'CallMeController@getuserdetailsbyid', 'as' => 'callme.getuserdetailsbyid']);
Route::post('/telecallercallduration', ['uses' => 'CallMeController@gettelecallercallduration', 'as' => 'callme.gettelecallercallduration']);
Route::post('/getprofilestatus', ['uses' => 'CallMeController@updatestatus_callcountapi', 'as' => 'callme.updatestatus_callcountapi']);
Route::post('/addplaystorerating', ['uses' => 'CallMeController@addplaystorerating', 'as' => 'callme.addplaystorerating']);
Route::post('/cm4doyouknow',['uses' => 'CallMeController@Cm4doyouknow', 'as' => 'callme.Cm4doyouknow']);
Route::post('/getusercalltime',['uses' => 'CallMeController@getUserCalltime', 'as' => 'callme.getUserCalltime']);
$router->get('/users/fetchcountry',
    ['uses' => 'UsersController@countryList', 'as' => 'users.countryList' , 'middleware' => 'permission']
);
Route::post('/cm4contactus',['uses' => 'CallMeController@CM4UserFeadback', 'as' => 'callme.CM4UserFeadback']);
Route::post('/GetSuggestedUser',['uses' => 'CallMeController@GetSuggestedUser', 'as' => 'callme.GetSuggestedUser']);
Route::post('/getCallStatus',['uses' => 'CallMeController@getCallStatus', 'as' => 'callme.getCallStatus']);
Route::post('/GetnearbyUser',['uses' => 'CallMeController@GetnearbyUser', 'as' => 'callme.GetnearbyUser']);
Route::post('/CM4VideoShootRequest',['uses' => 'CallMeController@CM4VideoShoot', 'as' => 'callme.CM4VideoShoot']);
Route::post('/CM4FbLogin',['uses' => 'CallMeController@CM4FbLogin', 'as' => 'callme.CM4FbLogin']);
Route::post('/Cm4CallsEarningRequest',['uses' => 'CallMeController@Cm4CallsEarning', 'as' => 'callme.Cm4CallsEarning']);
Route::post('/Cm4GetCallsEarningList',['uses' => 'CallMeController@Cm4GetCallsEarning', 'as' => 'callme.Cm4GetCallsEarning']);
//for Dynamic ads
Route::post('/GetUsersAds', ['uses' => 'CallMeController@GetAdsNewForUser', 'as' => 'callme.GetAdsNewForUser']);


Route::post('/Cm4HomefeedsDetails',['uses' => 'CallMeController@Cm4Homefeeds', 'as' => 'callme.Cm4Homefeeds']);
//HomeFeed IOS
Route::post('/Cm4HomefeedsDetailsios',['uses' => 'CallMeController@Cm4Homefeeds_ios', 'as' => 'callme.Cm4Homefeeds_ios']);


Route::post('/cm4_offers',['uses' => 'CallMeController@cm4_offers', 'as' => 'callme.cm4_offers']);

Route::post('/addMoneyPiggybank',['uses' => 'CallMeController@addMoneyPiggybank', 'as' => 'callme.addMoneyPiggybank']);

Route::post('/Cm4Earningads',['uses' => 'CallMeController@Cm4Earningads', 'as' => 'callme.Cm4Earningads']);
Route::post('/getTensionCode',['uses' => 'CallMeController@getReferalCode', 'as' => 'callme.getReferalCode']);
Route::post('/getusersearchiddetails',['uses' => 'CallMeController@getuser_searchid_details', 'as' => 'callme.getuser_searchid_details']);

//New API after Design Changes
//Dated :23-12-2017
Route::post('/reportuser',['uses' => 'CallMeController@Report_User_Request', 'as' => 'callme.Report_User_Request']);
Route::post('/createoffer',['uses' => 'CallMeController@Create_New_Offer', 'as' => 'callme.Create_New_Offer']);
Route::post('/fetchrateoffers',['uses' => 'CallMeController@Fetch_Offer_rate', 'as' => 'callme.Fetch_Offer_rate']);
Route::post('/setratetime',['uses' => 'CallMeController@set_Rate_Time', 'as' => 'callme.set_Rate_Time']);
Route::post('/canceloffer',['uses' => 'CallMeController@cancel_offer', 'as' => 'callme.cancel_offer']);
Route::post('/updateoffer',['uses' => 'CallMeController@Update_offer', 'as' => 'callme.Update_offer']);
Route::post('/updateuserstatus',['uses' => 'CallMeController@update_online_status', 'as' => 'callme.update_online_status']);
Route::post('/Cm4checkliveuser',['uses' => 'CallMeController@Cm4check_live_user', 'as' => 'callme.Cm4check_live_user']);
Route::post('/Cm4checkliveusernew',['uses' => 'CallMeController@Cm4check_live_user_new', 'as' => 'callme.Cm4check_live_user_new']);

Route::post('/Cm4getmyprofile',['uses' => 'CallMeController@getMyProfile', 'as' => 'callme.getMyProfile']);
Route::post('/Cm4getmyprofile_new',['uses' => 'CallMeController@getMyProfile_new', 'as' => 'callme.getMyProfile_new']);
Route::post('/usercallhistory', ['uses' => 'CallMeController@getusercallhistory', 'as' => 'callme.getusercallhistory']);

Route::post('/Cm4updatecomments',['uses' => 'CallMeController@update_comments', 'as' => 'callme.update_comments']);
Route::post('/Cm4checkupdateduser',['uses' => 'CallMeController@Cm4check_updated_user', 'as' => 'callme.Cm4check_updated_user']);
//Connected Users
Route::post('/Cm4connectedusers',['uses' => 'CallMeController@cm4_connected_users', 'as' => 'callme.cm4_connected_users']);



Route::post('/update_to_solr',['uses' => 'CallMeController@update_to_solr', 'as' => 'callme.update_to_solr']);
Route::post('/update_to_premium_search_solr',['uses' => 'CallMeController@update_to_premium_search_solr', 'as' => 'callme.update_to_premium_search_solr']);

/***********PROMOTER API ROUTING*******************/
Route::post('/login', ['uses' => 'CallMeController@user_login', 'as' => 'callme.user_login']);
Route::post('/addsurviour',['uses' => 'CallMeController@addSurviourUser', 'as' => 'callme.addSurviourUser']);
Route::post('/addpeoplebysurveyor',['uses' => 'CallMeController@addPeopleBysurveyor', 'as' => 'callme.addPeopleBysurveyor']);
Route::post('/fetchsurveyorlist',['uses' => 'CallMeController@fetchSurveyorlist', 'as' => 'callme.fetchSurveyorlist']);
Route::post('/surveyoraddedpeoplelist',['uses' => 'CallMeController@SurveyoraddedPeoplelist', 'as' => 'callme.SurvayoraddedPeoplelist']);
Route::post('/withdrawpromoterrequest',['uses' => 'CallMeController@withdrawPromoterRequest', 'as' => 'callme.withdrawPromoterRequest']);
Route::post('/Cm4checkcontact',['uses' => 'CallMeController@Cm4checkcontact', 'as' => 'callme.Cm4checkcontact']);
Route::post('/uploadaudio',['uses' => 'CallMeController@uploadaudio', 'as' => 'callme.uploadaudio']);

Route::post('/surveryorloc',['uses' => 'CallMeController@Cm4UpdateSurveyorsLoc', 'as' => 'callme.Cm4UpdateSurveyorsLoc']);
//Get Bloggers
Route::post('/getBloggers',['uses' => 'CallMeController@getBloggers', 'as' => 'callme.getBloggers']);
Route::post('/getVloggers',['uses' => 'CallMeController@getVloggers', 'as' => 'callme.getVloggers']);
// shradha code-----------//
Route::post('/getanotherotpcode', ['uses' => 'CallMeController@getanothercode', 'as' => 'callme.getanothercode' ]);
Route::post('/getverificationcode', ['uses' => 'CallMeController@verify_anothercode', 'as' => 'callme.verify_anothercode' ]);
Route::post('/getusecalltime', ['uses' => 'CallMeController@getusecalltime', 'as' => 'callme.getusecalltime' ]);
Route::post('/blockuser', ['uses' => 'CallMeController@insertblockuser', 'as' => 'callme.insertblockuser' ]);
Route::post('/getblockuser', ['uses' => 'CallMeController@getblockuser', 'as' => 'callme.getblockuser' ]);

Route::post('/getuserprofilebyid_new', ['uses' => 'CallMeController@getuserdetailsbyid_new', 'as' => 'callme.getuserdetailsbyid_new']);

/***********PROMOTER API ROUTING*******************/

//----------------------------------us api route------------------------------------------------------//
Route::post('/newUsdashboard',['uses' => 'UsCallMeController@newusdashboard', 'as' => 'callme.newusdashboard']);

Route::post('/CatUsdashboard',['uses' => 'UsCallMeController@catusdashboard', 'as' => 'callme.usdashboard']);

Route::post('/newUsupdateprofilebyuser', ['uses' => 'NewUsCallMeController@usupdateUserProfile', 'as' => 'callme.usupdateUserProfile']);

Route::post('/Usgetotpcode', ['uses' => 'UsCallMeController@getcode', 'as' => 'callme.getcode' ]);
Route::post('/Usotpverify', ['uses' => 'UsCallMeController@verify_otp', 'as' => 'callme.verify_otp' ]);
Route::post('/Usupdateusername', ['uses' => 'UsCallMeController@usupdateName', 'as' => 'callme.usupdateName' ]);
Route::post('/Usdashboard',['uses' => 'UsCallMeController@usdashboard', 'as' => 'callme.usdashboard']);
Route::post('/Uscm4offers',['uses' => 'UsCallMeController@uscm4offers', 'as' => 'callme.uscm4offers']);
Route::post('/Uscreateoffer',['uses' => 'UsCallMeController@uscreateNewOffer', 'as' => 'callme.uscreateNewOffer']);

Route::post('/Uscm4contactus',['uses' => 'UsCallMeController@usCM4UserFeadback', 'as' => 'callme.usCM4UserFeadback']);


Route::post('/Usblockuser', ['uses' => 'UsCallMeController@usinsertblockuser', 'as' => 'callme.usinsertblockuser' ]);


Route::post('/Usmarkfavourite', ['uses' => 'UsCallMeController@usmarkFavourite', 'as' => 'callme.usmarkFavourite' ]);

Route::post('/Usaddratingreview', ['uses' => 'UsCallMeController@usaddRatingReview', 'as' => 'callme.usaddRatingReview']);

Route::post('/Usuploadvideo', ['uses' => 'UsCallMeController@usuploadvideo', 'as' => 'callme.usuploadvideo']);

Route::post('/Usgetuserdashboard', ['uses' => 'UsCallMeController@usgetuserdashboard', 'as' => 'callme.usupdateUserProfile']);

Route::post('/Usnewupdateprofilebyuser', ['uses' => 'UsCallMeController@usnewupdateUserProfile', 'as' => 'callme.usnewupdateUserProfile']);

Route::post('/Usbankaccount', ['uses' => 'UsCallMeController@usbankaccount', 'as' => 'callme.usbankaccount']);

Route::post('/Usfetch_earn_expenses', ['uses' => 'UsCallMeController@usfetchearnexpenses', 'as' => 'callme.usfetchearnexpenses' ]);

Route::post('/UsAllfetch_earn_expenses', ['uses' => 'UsCallMeController@usallfetch_earn_expenses', 'as' => 'callme.usallfetch_earn_expenses' ]);

Route::post('/Usaddmoneytowallet', ['uses' => 'UsCallMeController@usaddmoneytowallet', 'as' => 'callme.usaddmoneytowallet' ]);

Route::post('/UsuserAmtReq',['uses' => 'UsCallMeController@uspaytmAmtReq', 'as' => 'callme.uspaytmAmtReq']);
Route::post('/Ustrantionhistroy',['uses' => 'UsCallMeController@ustrantionhistroy', 'as' => 'callme.ustrantionhistroy']);

Route::post('/Usviewprofile',['uses' => 'UsCallMeController@usviewprofile', 'as' => 'callme.usviewprofile']);

Route::post('/Uscm4callingapi', ['uses' => 'UsCallMeController@uscm4callingapi', 'as' => 'callme.uscm4callingapi']);

Route::post('/Usaddpaytmtransaction', ['uses' => 'UsCallMeController@usaddpaytmtransaction', 'as' => 'callme.cm4callapi']);

Route::post('/Ussearchnew_api', ['uses' => 'UsCallMeController@ussearchnewapi', 'as' => 'callme.ussearchnewapi' ]);
Route::post('/Usgetprofilestatus', ['uses' => 'UsCallMeController@usupdatestatus_callcountapi', 'as' => 'callme.usupdatestatus_callcountapi']);

Route::post('/Uscallnotification', ['uses' => 'UsCallMeController@uscall_notify', 'as' => 'callme.uscall_notify' ]);

Route::post('/Uscallsingleduration', ['uses' => 'UsCallMeController@uscallsingleduration', 'as' => 'callme.uscallsingleduration' ]);

Route::post('/Ustimedelay', ['uses' => 'UsCallMeController@ustimedeal', 'as' => 'callme.ustimedeal' ]);

Route::post('/Usgetsearchservicenew' ,['uses' => 'UsCallMeController@usgetsearchservicenew', 'as' => 'callme.usgetsearchservicenew']);
Route::post('/Usgetverificationcode', ['uses' => 'UsCallMeController@usverify_anothercode', 'as' => 'callme.usverify_anothercode' ]);
Route::post('/Usgetanotherotpcode', ['uses' => 'UsCallMeController@usgetanothercode', 'as' => 'callme.usgetanothercode' ]);
Route::post('/Ussolrcategorybypid' ,['uses' => 'UsCallMeController@usget_categorybypid', 'as' => 'callme.usget_categorybypid' ]);
Route::post('/UsCm4getmyprofile_new',['uses' => 'UsCallMeController@usgetMyProfile_new', 'as' => 'callme.usgetMyProfile_new']);

Route::post('/Ussetratetime',['uses' => 'UsCallMeController@usset_Rate_Time', 'as' => 'callme.usset_Rate_Time']);

Route::post('/Usuploadprofile',['uses' => 'UsCallMeController@usuploadprofile', 'as' => 'callme.usuploadprofile']);

Route::post('/UsNotification',['uses' => 'UsCallMeController@usNotification', 'as' => 'callme.usNotification']);

Route::post('/Usvideocallcost',['uses' => 'UsCallMeController@usvideocallcost', 'as' => 'callme.usvideocallcost']);

Route::post('/Usvideocall',['uses' => 'UsCallMeController@usvideocall', 'as' => 'callme.usvideocall']);

Route::post('/Usmisscallnotification',['uses' => 'UsCallMeController@usmisscallnotification', 'as' => 'callme.usmisscallnotification']);
Route::post('/Usgetcurrentlocation',['uses' => 'UsCallMeController@usgetcurrentlocation', 'as' => 'callme.usgetcurrentlocation']);

Route::post('/UsRecommandedApi', ['uses' => 'UsCallMeController@usrecommandedapi', 'as' => 'callme.usrecommandedapi' ]);
Route::post('/Uscalla2billing', ['uses' => 'UsCallMeController@uscalla2billing', 'as' => 'callme.uscalla2billing' ]);

Route::post('/Usupdate_to_solr',['uses' => 'UsCallMeController@usupdate_to_solr', 'as' => 'callme.usupdate_to_solr']);
Route::post('/Usupdate_to_premium_search_solr',['uses' => 'UsCallMeController@usupdate_to_premium_search_solr', 'as' => 'callme.usupdate_to_premium_search_solr']);

Route::post('/Usnumberotpverify', ['uses' => 'UsCallMeController@new_numotpverify', 'as' => 'callme.new_numotpverify' ]);
Route::post('/Usnumotpcode', ['uses' => 'UsCallMeController@usnumotpcode', 'as' => 'callme.usnumotpcode' ]);
//------------------------------------end -------------------------------------------------------------//

//Route::post('/users/register', array('uses' => 'UserController@create'));
Route::get('/telegram', function (){
          //TG::sendMsg('009717132393', 'Hello there!');
		  return TG::contactAdd('+918882799508' ,'Rajeev','Kumar');
     });

Route::get('/telegramList', function (){
          //TG::sendMsg('009717132393', 'Hello there!');
		 return TG::contactList();
     });	

Route::get('/telegramSend', function (){
          $res = TG::sendMsg('Rajeev_Kumar', 'Hello there!');
		  return ["val"=>$res];
		
     });	
Route::get('/telegramDialogList', function (){
          //TG::sendMsg('009717132393', 'Hello there!');
		 return TG::getDialogList();
		      });

Route::get('/CustomAuth', function (){
          //TG::sendMsg('009717132393', 'Hello there!');
		  $value = Request::header('username');
		   $value2 = Request::header('password');
		 return ['username'=>$value ,'password'=>$value2];
		      });			  
	 
