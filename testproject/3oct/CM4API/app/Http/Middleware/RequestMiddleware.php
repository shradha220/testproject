<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Request;
class RequestMiddleware implements Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	  return $request; die;
	   
        if($this->hasPermission($request)) {
        return $next($request);
       }
	
	$data = collect(["status"=> [ "code"=>"212","message"=>\Config::get('constants.results.212')  ] ]);
	   
       return response()->json($data,200);
	
   //  return "Access Denied";
    }
	
	 public function hasPermission($request){
	   //return $request;
	       $value = $request->header('Application/json');
               return $value;
		   $value2 = $request->header('DEVICE');
		   if($value==1 && $value2=="f3999b2cd78bdc5224f7a66e0c1aba41a5baef6"){
		    return true;
		   }else{
		   return false;
		   }
	 }
}
