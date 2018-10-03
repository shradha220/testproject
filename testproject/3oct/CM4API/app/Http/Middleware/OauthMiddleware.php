<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use OAuth2\HttpFoundationBridge\Request as OAuthRequest;


class OauthMiddleware
{
    
    public function handle($request, Closure $next)
    {
        //return $request->input('access_token');
        if(!$request->input('access_token'))
        {
            return response('Token not found',422);
        }
        $req=  \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $bridgedRequest=OAuthRequest::createFromRequest($req);
        $bridgedResponse=new \OAuth2\HttpFoundationBridge\Response();
        
        
        if(!$token=App::make('oauth2')->getAccessTokenData($bridgedRequest,$bridgedResponse))
        {
		//return $token;
            $response=App::make('oauth2')->getResponse();
            
            if($response->IsClientError() && $response->getParameter('error'))
            {
                if($response->getParameter('error')=='expired_token')
                {
                return response('The access token provided has expired',401);
                }
                return response('Invalid Token',422);
                
            }
            }
            else 
            {
            $request['user_id']=$token['user_id'];
             }
             
             return $next($request);
              
              //End of function
           }
    
    //End of Class
}
