<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Validators\RestValidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::resolver(function($translator, $data, $rules, $messages)
        {
            return new RestValidator($translator, $data, $rules, $messages);
        });

        \Validator::extend('utc_format', function ($attribute, $value, $parameters) {

            $ob =preg_match('/\\A(?:^((\\d{2}(([02468][048])|([13579][26]))[\\-\\/\\s]?((((0?[13578])|(1[02]))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])))))|(\\d{2}(([02468][1235679])|([13579][01345789]))[\\-\\/\\s]?((((0?[13578])|(1[02]))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\\-\\/\\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\\-\\/\\s]?((0?[1-9])|(1[0-9])|(2[0-8]))))))(\\s(((0?[0-9])|(1[0-9])|(2[0-3]))\\:([0-5][0-9])((\\s)|(\\:([0-5][0-9])))?))?$)\\z/', $value);
           // $ob =preg_match('([0-9]+):([0-5]?[0-9]):([0-5]?[0-9])', $value);
           // echo $ob;
            return $ob;
        });

        \Validator::extend('json_validor', function ($attribute, $value, $parameters) {
              //echo $value;
             if(is_array($value)){

             $data=    json_encode($value);
             }else{
                $data= $value;
             }
            //echo is_array($data)?"array":"string".$i.$data;

                json_decode($data);
             
            return (json_last_error() == JSON_ERROR_NONE);

        });


        \Validator::extend('url_validator', function ($attribute, $value, $parameters) {

//            if (!filter_var($value, FILTER_VALIDATE_URL) === false) {
//                echo("$value is a valid URL");
//            } else {
//                echo("$value is not a valid URL");
//            }
        return !filter_var($value, FILTER_VALIDATE_URL) === false;

    });


        \Validator::extend('alpha_spaces', function($attribute, $value)
        {
            return preg_match('/^[\pL\s]+$/u', $value);
        });

        \Validator::extend('phone', function($attribute, $value)
        {
            $mob="/^[1-9][0-9]*$/";
            return preg_match($mob, $value)  && strlen($value) == 10;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }



}
