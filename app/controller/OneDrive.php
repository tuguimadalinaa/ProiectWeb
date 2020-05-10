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
            }else{
                echo "lalalalaa";
            }
        }
        catch(Exception $e){
            echo json_encode(array("status"=>'401'));
        }
        
    }
    public static function UploadFile(){
        
    }
}
?>