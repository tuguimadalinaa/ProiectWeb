<?php
define('APP_REDIRECT_URI','http://localhost/ProiectWeb/app/home');
define('APP_KEY','ktix1g9yidkg1uh');
define('APP_SECRET','sc4obe9eblzyb5w');
Class Dropbox extends Controller{

    public static function GetCode(){
        $query = [
            'client_id' => APP_KEY,
            'response_type' => 'code',
        ];
        $http_query = http_build_query($query);
        $dropbox_authorize_uri = 'https://www.dropbox.com/oauth2/authorize' . '?' . $http_query . '&' . 'redirect_uri=' . APP_REDIRECT_URI;
        return $dropbox_authorize_uri;
    }

    public static function GetToken($code){
        $dropbox_token_uri = 'https://api.dropboxapi.com/oauth2/token';
        $URLparameters = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => APP_KEY,
            'client_secret' => APP_SECRET,
            'redirect_uri' => 'http://localhost/ProiectWeb/app/home'
       ];
       $URLparameters = http_build_query($URLparameters);
       $curl_resource = curl_init();
       curl_setopt($curl_resource,CURLOPT_URL,$dropbox_token_uri);
       curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
       curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
       curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array('Content-Type : application/x-www-form-urlencoded'));
       curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$URLparameters);
       $result = curl_exec($curl_resource);
       curl_close($curl_resource);
       $responseDecoded = json_decode($result,true);
        try{
            $acces_token = $responseDecoded['access_token'];
            if($acces_token!=null){
                echo json_encode(array("status"=>'200'));
            }
        }
        catch(Exception $e){
            echo json_encode(array("status"=>'401'));
        }
    }

}

