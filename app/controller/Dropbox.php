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
       $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        try{
            $access_token = $responseDecoded['access_token'];
            if($access_token!=null){
                self::getModel()->addAccessToken($access_token,$username,'Dropbox');
                echo json_encode(array("status"=>'200'));
            }
        }
        catch(Exception $e){
            echo json_encode(array("status"=>'401'));
        }
    }
    public static function uploadFile(){
        $dropbox_upload_url = "https://content.dropboxapi.com/2/files/upload";
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $filebits = "Hai noroc vecine";   //file content to upload
        $parameters = '{' .
            '"path": "/Langos/' . 'Costelino.txt' . '",' .
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
       return "http://localhost/ProiectWeb/app/home";
    }

    public static function getFolderFiles($folder_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $dropbox_listFiles_url = "https://api.dropboxapi.com/2/files/list_folder";
        if($folder_id == "root"){
            $folder_id = "";
        }
        $parameters = '{' .
             '"path": "' . $folder_id . '"' . ',' .
             '"recursive": false,' .
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
        $entries = $responseDecoded['entries'];
        //print_r(array_values($folders));
        $folders = array();
        foreach($entries as $file){
            if($file['.tag'] == 'folder' || $file['.tag'] == 'file'){
                array_push($folders,$file['name']);
                array_push($folders,$file['id']);
            }
        }        
        return json_encode($folders);
    }

    public static function getFileMetadata(){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://api.dropboxapi.com/2/files/get_metadata";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "/Langos/Biserica.txt",' .
            '"include_media_info": false,' .
            '"include_deleted": false,' .
            '"include_has_explicit_shared_members": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$parameter);
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

    public static function createFolder(){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_create_folder_url = "https://api.dropboxapi.com/2/files/create_folder_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $parameters = '{' .
            '"path": "/Termopane",' .
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

    public static function deleteItem($item_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_delete_item_url = "https://api.dropboxapi.com/2/files/delete_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $parameters = '{' .
            '"path": "' . $item_id . '"' .
        '}';
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_delete_item_url);
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

    public static function uploadSessionStart(){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://content.dropboxapi.com/2/files/upload_session/start";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"close": false' .
        '}';
        $file_data = 'Hello';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$file_data);
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Dropbox-API-Arg: $parameter",
            "Content-Type: application/octet-stream"
        ));
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        $session_id = $responseDecoded['session_id'];
        $file_size = strlen($file_data);
        $max_request_size = "157286400";
        if($file_size <= intval($max_request_size)){         
            Dropbox::uploadSessionFinish($token,$file_data,$session_id);
        } else {
            Dropbox::uploadSessionAppend($token,$file_data,$session_id);
        }
        echo $response;
    }

    public static function uploadSessionAppend($access_token,$file_data,$session_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://content.dropboxapi.com/2/files/upload_session/append_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameters = '{' .
            '"cursor" : {' .
                 '"session_id": ' . $session_id . ',' .
                 '"offset": 5' .
            '},' .
            '"close": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$file_data);
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Dropbox-API-Arg: $parameters",
            "Content-Type: application/octet-stream"
        ));
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        echo $response;
    }

    public static function uploadSessionFinish($access_token,$file_data,$session_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://content.dropboxapi.com/2/files/upload_session/finish";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameters = '{' .
            '"cursor" : {' .
                 '"session_id": ' . '"' . $session_id . '",' .
                 '"offset": 5' .
            '},' .
            '"commit": {' .
                '"path": "/Langos/Cartofi.txt",' .
                '"mode": "add",' . 
                '"autorename": true,' .
                '"mute": false,' .
                '"strict_conflict": false' .
            '}' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$file_data);
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Dropbox-API-Arg: $parameters",
            "Content-Type: application/octet-stream"
        ));
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        echo $response;
    }


    public static function download(){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://content.dropboxapi.com/2/files/download";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "/Langos/Biserica.txt"' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Dropbox-API-Arg: $parameter"
        ));
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response,true);
        echo $response;
    }

    public static function downloadByLink(){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_url = "https://api.dropboxapi.com/2/files/get_temporary_link";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "/Langos/Biserica.txt"' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$parameter);
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
    public static function Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path,$cookie_domain,$cookie_secure,$cookie_httponly){
        return self::getCookieHandler()->Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path,$cookie_domain,$cookie_secure,$cookie_httponly);
    }
}

