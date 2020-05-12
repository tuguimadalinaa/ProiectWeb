<?php
define('APP_KEY','ktix1g9yidkg1uh');
define('APP_SECRET','sc4obe9eblzyb5w');
Class Dropbox extends Controller{

    public static function GetCode(){
        $redirect_uri = "http://localhost/ProiectWeb/app/home";
        $query = [
            'client_id' => APP_KEY,
            'response_type' => 'code',
        ];
        $http_query = http_build_query($query);
        $dropbox_authorize_url = 'https://www.dropbox.com/oauth2/authorize' . '?' . $http_query . '&' . 'redirect_uri=' . $redirect_uri;
        return $dropbox_authorize_url;
    }

    public static function GetToken($code){
        $dropbox_token_url = 'https://api.dropboxapi.com/oauth2/token';
        $URLparameters = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => APP_KEY,
            'client_secret' => APP_SECRET,
            'redirect_uri' => 'http://localhost/ProiectWeb/app/home'
       ];
       $URLparameters = http_build_query($URLparameters);
       $curl_resource = curl_init();
       curl_setopt($curl_resource,CURLOPT_URL,$dropbox_token_url);
       curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
       curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
       curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array('Content-Type : application/x-www-form-urlencoded'));
       curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$URLparameters);
       $result = curl_exec($curl_resource);
       curl_close($curl_resource);
       $responseDecoded = json_decode($result,true);
        try{
            $access_token = $responseDecoded['access_token'];
            if($access_token!=null){
                self::getModel()->addAccessToken($access_token,'gigi@gmail.com');
                echo json_encode(array("status"=>'200'));
            }
        }
        catch(Exception $e){
            echo json_encode(array("status"=>'401'));
        }
    }
    public static function uploadFile(){
        $dropbox_upload_url = "https://content.dropboxapi.com/2/files/upload";
        $json_token = json_decode(self::getModel()->getAccessToken("gigi@gmail.com"),true); //gigi's token
        $token = $json_token['access_token'];
        $filebits = "Hai noroc vecine";   //file content to upload
        $parameters = '{' .
            '"path": "/Langos/' . 'Biserica.txt' . '",' .
            '"mode": "add",' .
            '"autorename": true,' .
            '"mute": false,' .
            '"strict_conflict": false' .
        '}';
       $curl_resource = curl_init();
       curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_url);
       curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
       curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
        "Authorization: Bearer ${token}",
        "Dropbox-API-Arg: " . $parameters,
        "Content-Type: application/octet-stream"
         ));
       curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
       curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
       curl_setopt($curl_resource,CURLOPT_POSTFIELDS, $filebits);
       $result = curl_exec($curl_resource);
       curl_close($curl_resource);
       $responseDecoded = json_decode($result,true);
       echo $result;
    }

    public static function getFolderFiles(){
        $json_token = json_decode(self::getModel()->getAccessToken("gigi@gmail.com"),true); //gigi's token
        $token = $json_token['access_token'];
        $dropbox_listFiles_url = "https://api.dropboxapi.com/2/files/list_folder";
        $parameters = '{' .
             '"path": "/Langos"' . ',' .
             '"recursive": true,' .
             '"include_media_info": false,' .
             '"include_deleted": false,' .
             '"include_has_explicit_shared_members": false,' .
             '"include_mounted_folders": true,' .
             '"include_non_downloadable_files": true' .
        '}';
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_listFiles_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: application/json"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$parameters);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        echo $response;
    }

    public static function createFolder(){
        $dropbox_create_folder_url = "https://api.dropboxapi.com/2/files/create_folder_v2";
        $json_token = json_decode(self::getModel()->getAccessToken("gigi@gmail.com"),true); //gigi's token
        $token = $json_token['access_token'];
        $parameters = '{' .
            '"path": "/Kurtos",' .
            '"autorename": false' .
        '}';
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_create_folder_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$parameters);
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Content-Type: application/json"
        ));
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        echo $response;
    }
}

