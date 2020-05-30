<?php
class OneDrive extends Controller{
    private static $client_id = '1f86c63b-65f3-48fb-a1e6-b161416059cf';
    private static $client_secret = '5FMDOtFz5z/6-/FqYJ-3.gG=YfcX2kAp';
    private static $url_code ='https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    private static $url_token = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private static $redirect_uri ='http://localhost/ProiectWeb/app/home';
    public static function GetCode(){
        $data=[
            'client_id' => self::$client_id,
            'client_secret'=> self::$client_secret,
            'scope'=>'offline_access Files.ReadWrite.All',
            'redirect_uri' => self::$redirect_uri,
            'response_type' =>'code',
        ];
        $query_string=http_build_query($data);
        return self::$url_code.'?'.$query_string;
    }
    public static function GetToken($code){
        $data=[
            'client_id' => self::$client_id,
            'scope'=>'offline_access Files.ReadWrite.All',
            'code' => $code,
            'redirect_uri' => self::$redirect_uri,
            'grant_type' => 'authorization_code',
            'client_secret'=> self::$client_secret,
        ];
        $query_string=http_build_query($data);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::$url_token,
            CURLOPT_USERAGENT => 'STOL2',
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => $query_string
        ]);
        $response=curl_exec($curl);
        curl_close($curl);
        $responseDecoded = json_decode($response,true);
        try{
            $access_token = $responseDecoded['access_token'];
            if($access_token!=null){
                $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                self::getModel()->addAccessToken($access_token,$username,'OneDrive');
                return json_encode(array("status"=>'200'));
            }
        }
        catch(Exception $e){
            return  json_encode(array("status"=>'401'));
        }
        
    }
    private static function getAccesTokenFromDB($username,$drive){
        $response = self::getModel()->getAccessToken($username,$drive);
        $json_response = json_decode($response,true);
        if($json_response['status']=='200'){
            return $json_response['access_token'];
        }else{
            return "fail";
        }
    }
    private static function createFile($fileName,$access_token){
        $data= json_encode(array('item'=>array(
            '@microsoft.graph.conflictBehavior'=>'rename')
        ));
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/root:/Documents/'.$fileName.':/createUploadSession',
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ".$access_token,
            "Content-Type: application/json"),
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POSTFIELDS=>$data
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    private static function WriteFile($fileName,$fileData,$access_token,$fileSize,$graph_url){
        $upload_curl=curl_init();
        curl_setopt_array($upload_curl,[
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_URL=>$graph_url,
            CURLOPT_CUSTOMREQUEST=>'PUT',
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                "Content-Type: application/octet-stream",
                                "Content-Length: ${fileSize}",
                                'Content-Range: bytes '."0-".($fileSize-1).'/'.$fileSize),
            CURLOPT_POSTFIELDS=>$fileData 
        ]);
        $response=curl_exec($upload_curl);
        curl_close($upload_curl);
        return $response;
    }
    
    public static function UploadFile($fileName, $fileData,$fileSize){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        if(strcmp($access_token,"fail")!=0){
            $response = self::createFile($fileName,$access_token);
            $json_response = json_decode($response,true);
            $graph_url = $json_response['uploadUrl'];
            $response = self::WriteFile($fileName,$fileData,$access_token,$fileSize,$graph_url);
            $decodedResponse = json_decode($response, true);
            $fileId = $decodedResponse['id'];
            return json_encode(array("status"=>'200',"id"=>$fileId));
        }
        return json_encode(array("status"=>'401'));
    }
    public static function UploadBigFile($fileName,$fileData,$fileSize,$readyToGo){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        if($readyToGo=="false"){
            if(strcmp($access_token,"fail")!=0){
                $response = self::createFile($fileName,$access_token);
                $json_response = json_decode($response,true);
                $graph_url = $json_response['uploadUrl'];
                $response = self::WriteFile($fileName,$fileData,$access_token,$fileSize,$graph_url);
                $decodedResponse = json_decode($response, true);
                $fileId = $decodedResponse['id'];
                return json_encode(array("status"=>'200',"id"=>$fileId));
            }
        }
        return json_encode(array("status"=>'200',"fileName"=>$fileName,"readyToGo"=>$readyToGo));
    }
    public static function makeRequestForFile($access_token,$fileName){
        
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me'.$fileName, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
           /* CURLOPT_CUSTOMREQUEST=>'GET',*/
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function GetFile($fileName){
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response = self::makeRequestForFile($access_token,$fileName);
        $decodedResponse = json_decode($response, true);
        $urlForDownload = $decodedResponse['@microsoft.graph.downloadUrl'];
        return json_encode(array("status"=>'200',"urlToDownload"=>$urlForDownload));
    }
    public static function makeRequestForListFiles($access_token,$fileName)
    {
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0//me'.$fileName.':/children', //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
           /* CURLOPT_CUSTOMREQUEST=>'GET',*/
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function ListAllFiles($fileName)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response = self::makeRequestForListFiles($access_token,$fileName);
        return $response;
    }
    public static function  downloadDirectoryRequest($access_token,$fileName)
    {
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me'.$fileName, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
           /* CURLOPT_CUSTOMREQUEST=>'GET',*/
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function downloadDirectory($fileName)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response = self::downloadDirectoryRequest($access_token,$fileName);
        return $response;
    }
    public static function deleteFileRequest($access_token,$id)
    {
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/items/'.$id, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_CUSTOMREQUEST=>'DELETE',
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function deleteFile($fileName)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response  = self::makeRequestForFile($access_token,$fileName);
        $decodedResponse = json_decode($response, true);
        $id = $decodedResponse['id'];
        $response = self::deleteFileRequest($access_token,$id);
        return $response;

    }
}
?>