<?php
namespace App\Util;

use GuzzleHttp\Client;

class Recaptcha {
    
    public static function verify($token) {
        $client = new Client();
        
        $response = $client->request('POST', config('app.recaptcha_api'), [
            'query' => [
                'secret' => config('app.recaptcha_secret'),
                'response' =>  $token
            ]
        ]);
                
        if($response->getStatusCode() == 200){
            $body = $response->getBody();
            
            if($body) {
                $obj = json_decode($body);
                
                return $obj->success;
            }
        }
            
        return false;
    }
    
}