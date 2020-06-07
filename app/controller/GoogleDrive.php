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

        public static function obtainUriForResumable($token,$fileName,$parent)
        {
        $metadata=array(
            "name"=>"${fileName}",
            "parents"=>array("${parent}")
        );
        $metadatajson=json_encode($metadata);
        //echo $metadatajson;
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
        public static function uploadSmallFileResumable($uri,$fileData)
        {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
        $token = $json_token['access_token'];
        $size=strlen($fileData);
        $curl_resource = curl_init();
        curl_setopt($curl_resource,CURLOPT_URL,$uri);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'PUT');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: application/octet-stream",
           "Content-Length: ${size}"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$fileData);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response=curl_exec($curl_resource);
        curl_close($curl_resource); 
        return  $response;
    }
        public static function uploadLargeFileResumable($uri,$chunkData,$startData,$endData,$sizeFile)
        {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
        $token = $json_token['access_token'];
        $size=strlen($chunkData);
        $curl_resource = curl_init();
        $endDataFixed=$endData-1;
        $range="${startData}" . "-" . "${endDataFixed}";
        $contentRange= "${range}" . " /" . "${sizeFile}";
        //$deCeNuMerge=array("sizeChunk"=>$size,"startData"=>$startData,"endData"=>$endData,"sizeFile"=>$sizeFile,"contentRange"=>$contentRange);

        curl_setopt($curl_resource,CURLOPT_URL,$uri);
        curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'PUT');
        curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
           "Authorization: Bearer ${token}",
           "Content-Type: application/octet-stream",
           "Content-Length: ${size}",
           "Content-Range: bytes $contentRange"
        ));
        curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$chunkData);
        curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
        $response=curl_exec($curl_resource);
        curl_close($curl_resource); 
        return json_encode($response);
        
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
        public static function createFolder($fileName,$fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $metadataArray=array( "name"=>"${fileName}","mimeType"=>"application/vnd.google-apps.folder", "parents"=>array("${fileId}"));
            $metadata=json_encode($metadataArray);
            //echo $token;
            $size=strlen($metadata);
    
    
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/drive/v3/files");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
               "Authorization: Bearer ${token}",
               "Content-Type: application/json"
            ));
            curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadata);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            curl_close($curl_resource);
            return $response;
            $responseDecoded = json_decode($response,true);
            //echo $responseDecoded;
        }

        
        
        public static function listAllFiles($fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            //echo $token;
            $uri="https://www.googleapis.com/drive/v3/files?q='${fileId}'+in+parents";
            $curl_resource=curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,$uri);
            curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Authorization: Bearer ${token}"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //echo $response;
            curl_close($curl_resource); 
            $responseDecoded = json_decode($response,true);
            //print_r(array_values($responseDecoded));
            $folders=array();
            foreach($responseDecoded['files'] as $file)
            {
                if(empty($file))
                {
                    
                }
                else{
                        array_push($folders,$file['name']);
                        array_push($folders,$file['id']);
                }
            }
        
            //print_r(array_values($folders));
            return json_encode($folders);
        
            
        }
        public static function getMetadata($fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $uri="https://www.googleapis.com/drive/v2/files/${fileId}";
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
            return  $response;
        }
        public static function downloadSmallFile($fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $metadata=self::getMetadata($fileId);
            $dataArray=json_decode($metadata,true);
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                $file_name = $dataArray['title'];
                
                $myfile = fopen("${file_name}","w");
                file_put_contents("${file_name}",$response);
                return $file_name;
        }
        public static function downloadLargeFile($fileId,$startData,$endData,$fileName)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $endDataFixed=$endData-1;
            $range="${startData}" . "-" . "${endDataFixed}";
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                 //"Content-Type: application/octet-stream"
                ));
                // 27564
                // CURLOPT_RANGE => $offset . '-' . ($offset + $chunk_size - 1),
                curl_setopt($curl_resource,CURLOPT_RANGE,$range);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                
                $myfile = fopen("${fileName}","w");
                file_put_contents("${fileName}",$response);
                //file_put_contents("C:\Users\alexg\Desktop\abc.jpg",$response);
                //echo $response;
                return $file_name;
            
        }
        public static function downloadFolder($fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            $metadata=self::getMetadata($fileId);
            $dataArray=json_decode($metadata,true);
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                 //"Content-Type: application/octet-stream"
                ));
                // 27564
                // CURLOPT_RANGE => $offset . '-' . ($offset + $chunk_size - 1),
                curl_setopt($curl_resource,CURLOPT_RANGE,"0");
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                $file_name = $dataArray['title'];
                
                $myfile = fopen("${file_name}","w");
                file_put_contents("${file_name}",$response);
                //file_put_contents("C:\Users\alexg\Desktop\abc.jpg",$response);
                //echo $response;
                return $file_name;
            
        }
        public static function deleteFile($fileId)
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
    
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}"
                ));
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                return json_encode($response);
            }
            public static function Cookie($cookie_name,$cookie_value,$cookie_params_array){
                return self::getCookieHandler()->Cookie($cookie_name,$cookie_value,$cookie_params_array);
            }
            public static function getParentFolderId($fileId)
            {
                $metadataFolder=self::getMetadata($fileId);
                 $metadataFolderArray=json_decode($metadataFolder,true);
                 if($metadataFolderArray["parents"][0]["id"]==null)
                 {
                     return "root";
                 }
                 else{
                 return  $metadataFolderArray["parents"][0]["id"];
                 
                }
            }   

        

            public static function renameFile($fileName,$fileId)
            {
                $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
                $token = $json_token['access_token'];
                $fileInfo=json_decode(self::getMetadata($fileId),true);
                //echo $token;
                $fileExtension=substr($fileInfo["title"],strrpos($fileInfo["title"],'.'));
                if($fileExtension==$fileInfo["title"])
                {
                    $metadataArray=array("name"=>"${fileName}");
                $metadata=json_encode($metadataArray);
                }
                else{
                    $metadataArray=array("name"=>"${fileName}"."${fileExtension}");
                    $metadata=json_encode($metadataArray);
                }
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Authorization: Bearer ${token}",
                "Content-Type: application/json"
                ));
                curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadata);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource); 
                return $response;
            }
            public static function moveFile($fileId,$fileIdToMove)
            {
                $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
                $token = $json_token['access_token'];
                $fileParent=self::getParentFolderId($fileId);
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?addParents=${fileIdToMove}&removeParents=${fileParent}";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource); 
                return $response;
            }
            public static function getSizeFile($fileId)
            {
               
                $response=self::getMetadata($fileId);
                $responseArray=json_decode($response,true);
                return $responseArray['fileSize'];
            }
            public static function getNameFile($fileId)
            {
                $response=self::getMetadata($fileId);
                $responseArray=json_decode($response,true);
                return $responseArray['title'];
            }

    }


       











    //     public static function uploadFile()
    // {
    //     $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
    //     $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
    //     $token = $json_token['access_token'];
    //     $metadata='--boundary' .
    //     ' Content-Type: application/json; charset=UTF-8' .
    //     ' {'  .
    //         '"name": "poleta.txt"' .
    //     ' }' .
    //     ' --boundary  ' .
    //     ' Content-Type: text/plain '  .
    //     '  "Mister"   '  .
    //     ' --boundary-- ' ;

    //     echo $metadata;
    //     //$metadata="--boundary 
    //     //Content-Type: application/json; charset=UTF-8

    //     //{
    //         //"name": "lala.txt"
    //     //}
    //     //--boundary
    //     //Content-Type: text/plain

    //     //"Mister"

    //     //--boundary--";
    //     $curl_resource = curl_init();
    //     curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart");
    //     curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
    //     curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
    //        "Authorization: Bearer ${token}",
    //        "Content-Type: multipart/related; boundary=boundary"
    //     ));
    //     curl_setopt($curl_resource,CURLOPT_POSTFIELDS,$metadata);
    //     curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
    //     curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
    //     $response=curl_exec($curl_resource);
    //     curl_close($curl_resource);
    //     echo $response;
    //     $responseDecoded = json_decode($response,true);
    // }
    // }
?>