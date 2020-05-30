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
                        self::getModel()->addAccessToken($access_token,$username,'GoogleDrive');
                        echo json_encode(array("status"=>'200'));
            }
        }   catch(Exception $e){
            echo json_encode(array("status"=>'401'));
            }
        
        }

        public static function obtainUriForResumable($token)
        {
        $metadata=array(
            "name"=>"gege.txt"
        );
        $metadatajson=json_encode($metadata);
        $size=strlen($metadatajson);
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable");
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: application/json; charset=UTF-8",
           "Content-Length: ${size}"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadatajson);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl_resource,CURLOPT_HEADER,TRUE);
        $response=curl_exec($curl_resource);
        curl_close($curl_resource); 
        //echo $response;
        $pos_location=strpos($response,"location");
        $uri=substr($response,$pos_location+10);
        $pos_uri=strpos($uri,"vary");
        $redirect_uri=substr($uri,0,$pos_uri-2);
        return  $redirect_uri;
        }



        public static function uploadFileResumable()
        {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
        $token = $json_token['access_token'];
        $uri=self::obtainUriForResumable($token);
        //echo $uri;
        $metadata="heheee";
        $size=strlen($metadata);
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,$uri);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'PUT');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: application/octet-stream"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadata);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response=curl_exec($curl_resource);
        curl_close($curl_resource); 
        echo $response;
        }
        public static function listAllFiles()
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            //echo $token;
            $uri="https://www.googleapis.com/drive/v3/files";
            $curl_resource=curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,$uri);
            curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Authorization: Bearer ${token}"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            curl_close($curl_resource); 
            $responseDecoded = json_decode($response,true);
            //print_r(array_values($responseDecoded));
            $folders=array();

            foreach($responseDecoded['files'] as $file)
            {
                if($file['mimeType']=='application/vnd.google-apps.folder')
                {
                    array_push($folders,$file['name']);
                    array_push($folders,$file['id']);
                }
                else{
                    array_push($folders,$file['name']);
                    array_push($folders,$file['id']);
                }
                    
            }
        
            //print_r(array_values($folders));
            return json_encode($folders);
            
        }

        public static function getMetadata()
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $uri="https://www.googleapis.com/drive/v3/files/${fileId}";
            $curl_resource=curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,$uri);
            curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Authorization: Bearer ${token}"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            curl_close($curl_resource); 
            echo $response;
        }
        public static function exportFolders()
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $files=self::listAllFiles();
            $files_length=count($files);
            for($i=0;$i<$files_length;$i+=2)
            {
                $fileId=$files[$i+1];
                $nameOfTheFile=$files[$i];
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}/export?mimeType=application/vnd.google-apps.folder";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}"
                ));
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                $data=$nameOfTheFile . " content: " . $response . "\n";  
                echo $data;
            }
        }
        public static function downloadAllFiles()
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $files=self::listAllFiles();
            $files_length=count($files);
            for($i=0;$i<$files_length;$i+=2)
            {
                $fileId=$files[$i+1];
                $nameOfTheFile=$files[$i];
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}"
                ));
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                $data=$nameOfTheFile . " content: " . $response . "\n";  
                echo $data;
            }
            
        }















        public static function uploadFile()
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
        $token = $json_token['access_token'];
        $metadata='--boundary' .
        ' Content-Type: application/json; charset=UTF-8' .
        ' {'  .
            '"name": "poleta.txt"' .
        ' }' .
        ' --boundary  ' .
        ' Content-Type: text/plain '  .
        '  "Mister"   '  .
        ' --boundary-- ' ;

        echo $metadata;
        //$metadata="--boundary 
        //Content-Type: application/json; charset=UTF-8

        //{
            //"name": "lala.txt"
        //}
        //--boundary
        //Content-Type: text/plain

        //"Mister"

        //--boundary--";
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart");
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: multipart/related; boundary=boundary"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadata);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response=curl_exec($curl_resource);
        curl_close($curl_resource);
        echo $response;
        $responseDecoded = json_decode($response,true);
    }
    }
?>