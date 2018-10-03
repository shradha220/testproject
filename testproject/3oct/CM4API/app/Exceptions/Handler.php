<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response as HttpResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
       // return parent::render($request, $e);
        
        
        
         if($this->isHttpException($e))
        {
            // echo "<pre>"; print_r($e->getStatusCode()); die;
             $token ="f3999b2cd78bdc5224f7a66e0c1aba41a5baef63cd84c4be5bb0e3cb2e3c0d26";
             switch ($e->getStatusCode()) {
                // not found
                case 404:
                    
//                  $returnData = array(
//                  'status' => 'error',
//                  'message' => 'URL NOT FOUND!'
//                  );
                    
                    $data = collect( ["status"=> ["code"=>"404","message"=>\Config::get('constants.results.404')],"device_key" => $token]);
                      return response()->json($data);

                break;
            
            case 405:
                    
//                  $returnData = array(
//                  'status' => 'error',
//                  'message' => 'Method Not Allowed!'
//                  );
//                      return response()->json($returnData);
                
                   $data = collect( ["status"=> ["code"=>"405","message"=>\Config::get('constants.results.405')],"device_key" => $token]);
                      return response()->json($data);

                break;
            
                case '500':
                    
//                   $returnData = array(
//                  'status' => 'error',
//                  'message' => 'Ineternal Server Error'
//                  );
//                      return response()->json($returnData,200);
                    
                       $data = collect( ["status"=> ["code"=>"500","message"=>\Config::get('constants.results.500')],"device_key" => $token]);
                      return response()->json($data);

                break;

                default:
                    
                      $data = collect( ["status"=> ["code"=>"400","message"=>\Config::get('constants.results.400')],"device_key" => $token]);
                      return response()->json($data);
                break;
            }
        }
        else
        {
                return parent::render($request, $e);
        }
    }
}
