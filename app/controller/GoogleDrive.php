<?php
    class GoogleDrive extends Controller{
        private static $google_client_id='18989996688-606poom9qjh2rgcbq6e1e4phk0lsp0c7.apps.googleusercontent.com';
        private static $google_client_secret='aha78mJ7nTnZoezOc2GagCJs';
        private static $google_redirect_uri='http://localhost/ProiectWeb/app/home';
        private static $google_site='https://accounts.google.com/o/oauth2/v2/auth';
        public static function GetCode()
        {
           $query=[
               'scope'=>"https://www.googleapis.com/auth/drive",
                'response_type'=>'code',
                'redirect_uri'=>self::$google_redirect_uri,
                'client_id'=>self::$google_client_id
           ];
           $query_string=http_build_query($query);
           $google_drive_uri=self::$google_site.'?'. $query_string;
           return $google_drive_uri;
           
        }
        public static function GetToken($code){
            $query=[
                'code'=>$code,
                'client_id'=>self::$google_client_id,
                'client_secret'=>self::$google_client_secret,
                'redirect_uri'=>self::$google_redirect_uri,
                'grant_type'=>"authorization_code"
            ];
            $query_string=http_build_query($query);
            $curl=curl_init();
            curl_setopt_array($curl,[
                CURLOPT_URL => 'https://oauth2.googleapis.com/token',
                CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_POST => 1,
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
        }   catch(Exception $e){
            echo json_encode(array("status"=>'401'));
            }
        
        }
    }
?>