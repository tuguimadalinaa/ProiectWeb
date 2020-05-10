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
                self::getModel()->addAccessToken($access_token,'cici@gmail.com');
                echo json_encode(array("status"=>'200'));
            }
        }
        catch(Exception $e){
            echo json_encode(array("status"=>'401'));
        }
        
    }
    private static function getAccesTokenFromDB(){
        $response = self::getModel()->getAccessToken('cici@gmail.com');
        $json_response = json_decode($response,true);
        if($json_response['status']=='200'){
            return $json_response['access_token'];
        }else{
            return "fail";
        }
    }
    private static function createFile($fileName,$access_token){
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/root:/Documents/'.$fileName.':/content',
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_PUT=>true,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ".$access_token,
            "Content-Type: text/plain"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    private static function WriteFile($fileName,$fileData,$access_token,$fileSize){
        $upload_curl=curl_init();
        curl_setopt_array($upload_curl,[
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/root:/Documents/'.$fileName.':/content',
            CURLOPT_PUT=>true,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ".$access_token,
                                'Content-Length: '.$fileSize,
                                'Content-Range: bytes '.'0-'.($fileSize-1).'/'.$fileSize),
            CURLOPT_POSTFIELDS=>$fileData 
        ]);
        $response=curl_exec($upload_curl);
        curl_close($upload_curl);
        return $response;
    }
    public static function UploadFile($fileName, $fileData,$fileSize){
        $access_token = self::getAccesTokenFromDB();
        if(strcmp($access_token,"fail")!=0){
            $response = self::createFile($fileName,$access_token);
            $json_response = json_decode($response,true);
            $graph_url = $json_response['uploadUrl'];
            return json_encode(array("status"=>$graph_url));
            $response = self::WriteFile($fileName,$fileData,$access_token,$fileSize);
        }
        return $response;
    }
}
?>