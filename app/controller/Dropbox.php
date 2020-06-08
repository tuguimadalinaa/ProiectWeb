<?php
Class Dropbox extends Controller{

    public static function GetCode(){
        $redirect_uri = "http://localhost/ProiectWeb/app/home";
        $app_key = 'ktix1g9yidkg1uh';
        $query = [
            'client_id' => $app_key,
            'response_type' => 'code',
        ];
        $http_query = http_build_query($query);
        $dropbox_authorize_url = 'https://www.dropbox.com/oauth2/authorize' . '?' . $http_query . '&' . 'redirect_uri=' . $redirect_uri;
        return $dropbox_authorize_url;
    }

    public static function GetToken($code){
        $app_secret = 'sc4obe9eblzyb5w';
        $app_key = 'ktix1g9yidkg1uh';
        $dropbox_token_url = 'https://api.dropboxapi.com/oauth2/token';
        $URLparameters = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $app_key,
            'client_secret' => $app_secret,
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

    public static function uploadSmallFile($file_data,$file_path_array){
        $dropbox_upload_url = "https://content.dropboxapi.com/2/files/upload";
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $file_path = $file_path_array['path'];
        if(strpos($file_path,'/') == false){
            $current_folder_id = $_COOKIE["Dropbox"];
            $current_folder_metadata= self::getItemMetadata($current_folder_id);
            $current_folder_path = $current_folder_metadata['path_display'];
            $path = $current_folder_path . '/' . $file_path;
        } else {
            $path = $file_path;
        }
        $parameters = '{' .
            '"path": "' . $path . '",' .
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
       curl_setopt($curl_resource,CURLOPT_POSTFIELDS, $file_data);
       $result = curl_exec($curl_resource);
       curl_close($curl_resource);
       $responseDecoded = json_decode($result,true);
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

    public static function getItemMetadata($item_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_metadata_url = "https://api.dropboxapi.com/2/files/get_metadata";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "' . $item_id . '",' .
            '"include_media_info": false,' .
            '"include_deleted": false,' .
            '"include_has_explicit_shared_members": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_metadata_url);
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
        return $responseDecoded;
    }

    public static function createFolder($current_folder_id,$folder_name){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_create_folder_url = "https://api.dropboxapi.com/2/files/create_folder_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        if($current_folder_id != 'root'){
            $metadata = self::getItemMetadata($current_folder_id);
            $current_folder_path = $metadata['path_display'];
            echo ($current_folder_path . '/' . $folder_name);
            $parameters = '{' .
                '"path": "' . $current_folder_path . '/' . $folder_name . '",' .
                '"autorename": false' .
            '}';
        } else {
            $parameters = '{' .
                '"path": "' . '/' . $folder_name . '",' .
                '"autorename": false' .
            '}';
        }
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

    public static function uploadSessionStart($file_data){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_upload_session_start_url = "https://content.dropboxapi.com/2/files/upload_session/start";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"close": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_start_url);
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
        return $session_id;
    }

    public static function uploadSessionAppend($file_data,$cursor_id,$offset){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_upload_session_append_url = "https://content.dropboxapi.com/2/files/upload_session/append_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameters = '{' .
            '"cursor" : {' .
                 '"session_id": "' . $cursor_id . '",' .
                 '"offset": ' . $offset . 
            '},' .
            '"close": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_append_url);
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
        return 'Chunk uploaded';
    }

    public static function uploadSessionFinish($file_data,$cursor_id,$offset,$file_name,$parent_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_upload_session_finish_url = "https://content.dropboxapi.com/2/files/upload_session/finish";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $parent_metadata = self::getItemMetadata($parent_id);
        $parent_path = $parent_metadata['path_display'];
        $curl_resource = curl_init();
        $parameters = '{' .
            '"cursor" : {' .
                 '"session_id": ' . '"' . $cursor_id . '",' .
                 '"offset": ' . $offset .
            '},' .
            '"commit": {' .
                '"path": "' . $parent_path . '/' . $file_name . '",' . 
                '"mode": "add",' . 
                '"autorename": true,' .
                '"mute": false,' .
                '"strict_conflict": false' .
            '}' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_finish_url);
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
        echo $parameters;
    }


    public static function downloadFile(){
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

    public static function downloadFileByLink($file_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_link_url = "https://api.dropboxapi.com/2/files/get_temporary_link";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "' . $file_id . '"' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_link_url);
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
        //echo $response;
        return $responseDecoded['link'];
    }
    public static function Cookie($cookie_name,$cookie_value,$cookie_params_array){
        return self::getCookieHandler()->Cookie($cookie_name,$cookie_value,$cookie_params_array);
    }

    public static function downloadFolder($folder_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_download_folder_zip_url = "https://content.dropboxapi.com/2/files/download_zip";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        //echo $token;
        $metadata = self::getItemMetadata($folder_id);
        $curl_resource = curl_init();
        $parameter = '{' .
            '"path": "' . $folder_id . '"' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_download_folder_zip_url);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Authorization: Bearer ${token}",
            "Dropbox-API-Arg: ${parameter}"
        ));
        //curl_setopt($curl_resource,CURLOPT_HEADER,TRUE);
        curl_setopt($curl_resource,CURLOPT_FOLLOWLOCATION,0);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response = curl_exec($curl_resource);
        curl_close($curl_resource);
        $responseDecoded = json_decode($response);
        $file_name = $metadata['name'];
        $myfile = fopen("${file_name}.zip","w");
        file_put_contents("${file_name}.zip",$response);
        return $file_name;
    }

    public static function getParentFolderId($current_folder_id){
        $current_folder_metadata = self::getItemMetadata($current_folder_id);
        $current_folder_name_position_in_path = strpos($current_folder_metadata['path_display'],$current_folder_metadata['name']);
        $parent_path = substr($current_folder_metadata['path_display'],0,$current_folder_name_position_in_path - 1);
        if($parent_path == null){
            return 'root';
        } else {
            $parent_folder_metadata = self::getItemMetadata($parent_path);
            return $parent_folder_metadata['id'];
        }
    }

    public static function renameItem($item_id,$new_name){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_rename_item_url = "https://api.dropboxapi.com/2/files/move_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $metadata = self::getItemMetadata($item_id);
        $item_path = $metadata['path_display'];
        $item_extension = substr($item_path,strpos($item_path,'.'));
        $item_parent_path = substr($item_path,0,strrpos($item_path,'/')+1);
        $curl_resource = curl_init();
        $parameters = '{' .
            '"from_path": "' . $item_path . '",' .
            '"to_path": "' . $item_parent_path . $new_name . $item_extension . '",' .
            '"allow_shared_folder": false,' .
            '"autorename": false,' .
            '"allow_ownership_transfer": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_rename_item_url);
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
        $responseDecoded = json_decode($response);
        return $response;
    }

    public static function moveItem($current_folder_id,$item_id){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $dropbox_rename_item_url = "https://api.dropboxapi.com/2/files/move_v2";
        $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
        $token = $json_token['access_token'];
        $item_metadata = self::getItemMetadata($item_id);
        $current_folder_metadata = self::getItemMetadata($current_folder_id);
        $item_path = $item_metadata['path_display'];
        $item_name = $item_metadata['name'];
        $current_folder_path = $current_folder_metadata['path_display'];
        $curl_resource = curl_init();
        $parameters = '{' .
            '"from_path": "' . $item_path . '",' .
            '"to_path": "' . $current_folder_path . '/' . $item_name . '",' .
            '"allow_shared_folder": false,' .
            '"autorename": false,' .
            '"allow_ownership_transfer": false' .
        '}';
        curl_setopt($curl_resource,CURLOPT_URL,$dropbox_rename_item_url);
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
        $responseDecoded = json_decode($response);
        return $response;
    }


/* --------------------------------------------- Dropbox API functions ---------------------------------------------- */
    
public static function APIGetCode(){
    $redirect_uri = "http://localhost/ProiectWeb/app/APIhome1";
    $app_key = 'ktix1g9yidkg1uh';
    $query = [
        'client_id' => $app_key,
        'response_type' => 'code',
    ];
    $http_query = http_build_query($query);
    $dropbox_authorize_url = 'https://www.dropbox.com/oauth2/authorize' . '?' . $http_query . '&' . 'redirect_uri=' . $redirect_uri;
    //$dropbox_authorize_url = 'https://www.dropbox.com/oauth2/authorize' . '?' . $http_query;
    return $dropbox_authorize_url;
}

public static function APIGetToken($code,$jwt){
    $app_secret = 'sc4obe9eblzyb5w';
    $app_key = 'ktix1g9yidkg1uh';
    $dropbox_token_url = 'https://api.dropboxapi.com/oauth2/token';
    $URLparameters = [
        'code' => $code,
        'grant_type' => 'authorization_code',
        'client_id' => $app_key,
        'client_secret' => $app_secret,
        'redirect_uri' => 'http://localhost/ProiectWeb/app/APIhome1'
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
   $username=(self::getAuth()->jwtDecode($jwt))->username;
   //return $result;
    try{
        if($responseDecoded['access_token'] != null){
            $access_token = $responseDecoded['access_token'];
            if($access_token!=null){
                self::getModel()->addAccessToken($access_token,$username,'Dropbox');
                return 'Access Granted';
            } else {
                return 'Null token';
            }
        } else {
            return 'Null token';
        }
    }
    catch(Exception $e){
        return 'Invalid code';
    }
}

public static function uploadSmallFileAPI($file_data,$file_name,$username){
    $dropbox_upload_url = "https://content.dropboxapi.com/2/files/upload";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $parameters = '{' .
        '"path": "' . '/' . $file_name . '",' .
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
   curl_setopt($curl_resource,CURLOPT_POSTFIELDS, $file_data);
   $result = curl_exec($curl_resource);
   curl_close($curl_resource);
   $responseDecoded = json_decode($result,true);
}

public static function uploadLargeFileAPI($file_data,$file_name,$username){
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $file_size = strlen($file_data);
    $uploaded_data = 0;
    $upload_session_start = 0;
    $session_id = 0;
    $max_upload_data = 1048576 * 40;
    while($file_size - $uploaded_data > $max_upload_data){
        $chunk = substr($file_data,$uploaded_data,$max_upload_data);
        if($upload_session_start == 0){
            $session_id = Dropbox::uploadSessionStartAPI($chunk,$username);
            $upload_session_start = 1;
        } else {
            $responseAppend = Dropbox::uploadSessionAppendAPI($chunk,$session_id,$uploaded_data,$username);
        }
        $uploaded_data = $uploaded_data + $max_upload_data;
    }
    if($file_size - $uploaded_data < $max_upload_data ){
        $chunk = substr($file_data,$uploaded_data,$max_upload_data);
        $responseFinish = Dropbox::uploadSessionFinishAPI($chunk,$session_id,$uploaded_data,$file_name,$username);
    }
}

public static function uploadSessionStartAPI($file_data,$username){
    $dropbox_upload_session_start_url = "https://content.dropboxapi.com/2/files/upload_session/start";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $curl_resource = curl_init();
    $parameter = '{' .
        '"close": false' .
    '}';
    curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_start_url);
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
    return $session_id;
}

public static function uploadSessionAppendAPI($file_data,$session_id,$offset,$username){
    $dropbox_upload_session_append_url = "https://content.dropboxapi.com/2/files/upload_session/append_v2";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $curl_resource = curl_init();
    $parameters = '{' .
        '"cursor" : {' .
             '"session_id": "' . $session_id . '",' .
             '"offset": ' . $offset . 
        '},' .
        '"close": false' .
    '}';
    curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_append_url);
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
    return 'Chunk uploaded';
}

public static function uploadSessionFinishAPI($file_data,$session_id,$offset,$file_name,$username){
    $dropbox_upload_session_finish_url = "https://content.dropboxapi.com/2/files/upload_session/finish";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $curl_resource = curl_init();
    $parameters = '{' .
        '"cursor" : {' .
             '"session_id": ' . '"' . $session_id . '",' .
             '"offset": ' . $offset .
        '},' .
        '"commit": {' .
            '"path": "' . '/' . $file_name . '",' . 
            '"mode": "add",' . 
            '"autorename": true,' .
            '"mute": false,' .
            '"strict_conflict": false' .
        '}' .
    '}';
    curl_setopt($curl_resource,CURLOPT_URL,$dropbox_upload_session_finish_url);
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
}

public static function getItemMetadataAPI($file_id,$username){
    $dropbox_metadata_url = "https://api.dropboxapi.com/2/files/get_metadata";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $curl_resource = curl_init();
    $parameter = '{' .
        '"path": "' . $file_id . '",' .
        '"include_media_info": false,' .
        '"include_deleted": false,' .
        '"include_has_explicit_shared_members": false' .
    '}';
    curl_setopt($curl_resource,CURLOPT_URL,$dropbox_metadata_url);
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
    return $responseDecoded;
}

public static function checkIfFileExist($file_name,$username){
    $file_name_dropbox = '/3' . $username . $file_name;
    $file_metadata = self::getItemMetadataAPI($file_name_dropbox,$username);
    $file_name_drop = "3" . $username . $file_name;
    if($file_metadata != null){
        if($file_metadata['name'] == $file_name_drop){
            return 1;
        } 
        return 0;
    } else {
        return 0;
    }
}

public static function downloadFileAPI($file_name,$username){
    $dropbox_download_url = "https://content.dropboxapi.com/2/files/download";
    $json_token = json_decode(self::getModel()->getAccessToken($username,'Dropbox'),true);
    $token = $json_token['access_token'];
    $file_name_dropbox = "3" . $username . $file_name;
    $curl_resource = curl_init();
    $parameter = '{' .
        '"path": "/' . $file_name_dropbox . '"' .
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
    $file_created = file_put_contents($file_name,$response,FILE_APPEND);
    $responseDecoded = json_decode($response,true);
    return 'Peste';
}
}
?>