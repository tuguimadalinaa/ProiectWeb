<?php


//include 'C:\xampp\htdocs\ProiectWeb\app\models\auth_jwt.php';
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
        public static function GetToken($code,$data){
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
            $username=(self::getAuth()->jwtDecode($data))->username;
            try{
                $access_token = $responseDecoded['access_token'];
                    if($access_token!=null){
                        self::getModel()->addAccessToken($access_token,$username);
                        echo json_encode(array("status"=>'200'));
            }
        }   catch(Exception $e){
            echo json_encode(array("status"=>'401'));
            }
        
        }
        /*public static function UploadFile()
        {
            $string = 'SELECT access_token from users where username="cici@gmail.com"';
            $connection  = DataBase::connect()->prepare($string);
            $connection->execute();
            $token = $connection -> fetchAll();
            $path='C:\Users\alexg\Desktop\download.jpg';
            $file=file_get_contents($path);
            $headers= array(
                'Content-Type:image/jpeg'
                'Authorization : Bearer ' . $token,
                'Content-Length: ' . strlen($file)
            );
            $curl=curl_init();
            curl_setopt_array($curl,[
                CURLOPT_URL => 'https://www.googleapis.com/upload/drive/v2/files?uploadType=media',
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER=>TRUE,
                CURLOPT_BINARYTRANSFER=>TRUE,
                CURLOPT_POST=>TRUE,
                CURLOPT_POSTFIELDS=>$file
            ]);
            $response=curl_exec($curl);
            curl_close($curl);
            echo $response;
        }
        */
    }
?>