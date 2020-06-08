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
        public static function getTokenUser()
        {
            $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            return $token;
        }
        public static function getInfoToken()
        {

            $token = self::getTokenUser();
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=${token}");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'GET');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type: application/x-www-form-urlencoded"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            curl_close($curl_resource);
            $responseDecoded=json_decode($response,true); 
            return $responseDecoded['expires_in'];
        }
        public static function getStorageQuota()
        {
            $token = self::getTokenUser();
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL,"https://www.googleapis.com/drive/v3/about?fields=storageQuota");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'GET');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
               "Authorization: Bearer ${token}"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true);
            $limit=$responseDecoded['storageQuota']['limit'];
            $usage=$responseDecoded['storageQuota']['usage'];
            return $limit-$usage;
            //return $responseDecoded['storageQuota']['limit']-$responseDecoded['storageQuota']['usage'];
        }
        public static function obtainUriForResumable($fileName,$parent)
        {
            $token = self::getTokenUser();
        $metadata=array(
            "name"=>"${fileName}",
            "parents"=>array("${parent}")
        );
        if($parent==null)
        {
            $metadata=array(
                "name"=>"${fileName}"
            );
        }
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
        $pos_location=strpos($response,"location");
        $uri=substr($response,$pos_location+10);
        $pos_uri=strpos($uri,"vary");
        $redirect_uri=substr($uri,0,$pos_uri-2);
        return  $redirect_uri;
        }
        public static function uploadSmallFileResumable($uri,$fileData)
        {
            $token = self::getTokenUser();
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
            $token = self::getTokenUser();
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
            $token = self::getTokenUser();
        $uri=self::obtainUriForResumable($token,null,null);
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
            $token = self::getTokenUser();
            $metadataArray=array( "name"=>"${fileName}","mimeType"=>"application/vnd.google-apps.folder", "parents"=>array("${fileId}"));
            $metadata=json_encode($metadataArray);
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
        }

        
        
        public static function listAllFiles($fileId)
        {
            $token = self::getTokenUser();
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
 
            curl_close($curl_resource); 
            $responseDecoded = json_decode($response,true);

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
        

            return json_encode($folders);
        
            
        }
        public static function getMetadata($fileId)
        {
            $token = self::getTokenUser();
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
            $token = self::getTokenUser();
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
        public static function downloadLargeFile($fileId)
        {
            $token = self::getTokenUser();
            $metadata=self::getMetadata($fileId);
            $dataArray=json_decode($metadata,true);
            $startData=0;
            $contor="Face ce trebuie";
            $fileSize=$dataArray['fileSize'];
            $file_name=$dataArray['title'];
            $maxDownloadSize=256 * 1024 * 128;
            $endDataFixed=$maxDownloadSize-1;
            while($fileSize-$startData>=$maxDownloadSize)
            {
                $range="${startData}" . "-" . "${endDataFixed}";
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RANGE,$range);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                
                file_put_contents("${file_name}",$response,FILE_APPEND);
                $startData=$startData+$maxDownloadSize;
                $endDataFixed=$endDataFixed+$maxDownloadSize;
            }
            if($fileSize-$startData==0)
            {
                return $file_name;
            }
            else if($fileSize-$startData<$maxDownloadSize)
            {
                $endDataFixed=$fileSize-1;
                $range="${startData}" . "-" . "${endDataFixed}";
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RANGE,$range);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                
                file_put_contents("${file_name}",$response,FILE_APPEND);
                $startData=$startData+$maxDownloadSize;
                $endDataFixed=$endDataFixed+$maxDownloadSize;
                return $file_name;
            }
        }
        public static function deleteFile($fileId)
        {
            $token = self::getTokenUser();
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
                $token = self::getTokenUser();
                $fileInfo=json_decode(self::getMetadata($fileId),true);

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
                $token = self::getTokenUser();
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



/* --------------------------------------------- GoogleDrive API functions ---------------------------------------------- */

public static function APIGetCode()
{
    $redirect_uri='http://localhost/ProiectWeb/app/APIhome1';
    $query=[
        'scope'=>"https://www.googleapis.com/auth/drive",
         'response_type'=>'code',
         'redirect_uri'=>$redirect_uri,
         'client_id'=>self::$google_client_id
    ];
    $query_string=http_build_query($query);
    $google_drive_uri=self::$google_site.'?'. $query_string;
    return $google_drive_uri;
}
public static function APIGetToken($code,$jwt){
    $redirect_uri='http://localhost/ProiectWeb/app/APIhome1';
    $query=[
        'code'=>$code,
        'client_id'=>self::$google_client_id,
        'client_secret'=>self::$google_client_secret,
        'redirect_uri'=>$redirect_uri,
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
    $username=(self::getAuth()->jwtDecode($jwt))->username;
    try{
        $access_token = $responseDecoded['access_token'];
            if($access_token!=null){
                self::getModel()->addAccessToken($access_token,$username,'GoogleDrive');
                return 'Access Granted';
    }
    else{
        return 'Null token';
    }
}   catch(Exception $e){
    return 'Invalid code';
    }

}      
    public static function getTokenAPI($username)
        {
            $json_token = json_decode(self::getModel()->getAccessToken($username,'GoogleDrive'),true);
            $token = $json_token['access_token'];
            return $token;
        }
    public static function uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username)
    {
        $token = self::getTokenAPI($username);
        $uri=self::obtainUriForResumableAPI($token,$googledrive_file_name,null);
        $response=self::uploadSmallFileResumableAPI($uri,$googledrive_data,$username);
        $responseDecoded=json_decode($response,true);
         return $responseDecoded['id'];
    }
    public static function uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username)
    {
        $token = self::getTokenAPI($username);
        $uri=self::obtainUriForResumableAPI($token,$googledrive_file_name,null);
        $sizeFile=strlen($googledrive_data);
        $maxUploadSize=256 * 1024 * 128;//32 mb
        $uploadData=0;
        while($sizeFile-$uploadData>$maxUploadSize)
        {
            $dataSlice=substr($googledrive_data,$uploadData,$maxUploadSize);
            $response=self::uploadLargeFileResumableAPI($uri,$dataSlice,$uploadData,$uploadData+$maxUploadSize-1,$sizeFile,$username);
            $uploadData=$uploadData+$maxUploadSize;
            
        }
        if($sizeFile-$uploadData<$maxUploadSize)
        {
            $dataSlice=substr($googledrive_data,$uploadData,$sizeFile);
            $response=self::uploadLargeFileResumableAPI($uri,$dataSlice,$uploadData,$sizeFile-1,$sizeFile,$username);
        }
        $responseDecoded=json_decode($response,true);
         return $responseDecoded['id'];
    }
    public static function obtainUriForResumableAPI($token,$fileName,$parent)
        {
        $metadata=array(
            "name"=>"${fileName}",
            "parents"=>array("${parent}")
        );
        if($parent==null)
        {
            $metadata=array(
                "name"=>"${fileName}"
            );
        }
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
        $pos_location=strpos($response,"location");
        $uri=substr($response,$pos_location+10);
        $pos_uri=strpos($uri,"vary");
        $redirect_uri=substr($uri,0,$pos_uri-2);
        return  $redirect_uri;
        }
    public static function uploadSmallFileResumableAPI($uri,$fileData,$username)
    {
        $token = self::getTokenAPI($username);
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
    public static function uploadLargeFileResumableAPI($uri,$chunkData,$startData,$endData,$sizeFile,$username)
    {
        $token = self::getTokenAPI($username);
    $size=strlen($chunkData);
    $curl_resource = curl_init();
    $endDataFixed=$endData;
    $range="${startData}" . "-" . "${endDataFixed}";
    $contentRange= "${range}" . " /" . "${sizeFile}";
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
    return $response;
    
}

    public static function checkIfFileExistGoogleDrive($file_name,$username,$googledriveId)
    {
        $response=self::getMetadataAPI($googledriveId,$username);
        $responseDecoded=json_decode($response,true);
        $file_name_googledrive = '1' . $username . $file_name;
        $fileNameFromMetadata=$responseDecoded['title'];
        if($file_name_googledrive==$fileNameFromMetadata)
        {
            return 1;
        }
        else 
        {
            return 0;
        }
        
    }

    public static function downloadSmallFilesAPI($googledriveId,$username,$file_name)
    {
        $response=self::downloadSmallFileAPI($googledriveId,$username,$file_name);
        return $response;
    }
    public static function downloadLargeFilesAPI($googledriveId,$username,$file_name)
    {
        $response=self::downloadLargeFileAPI($googledriveId,$username,$file_name);
        return $response;
    }
    public static function downloadSmallFileAPI($fileId,$username,$file_name)
        {
            $token = self::getTokenAPI($username);
            $metadata=self::getMetadataAPI($fileId,$username);
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
                file_put_contents("${file_name}",$response,FILE_APPEND);
                return $file_name;
        }

        public static function downloadLargeFileAPI($fileId,$username,$file_name)
        {
            $token = self::getTokenAPI($username);
            $metadata=self::getMetadataAPI($fileId,$username);
            $dataArray=json_decode($metadata,true);
            $startData=0;
            $contor="Face ce trebuie";
            $fileSize=$dataArray['fileSize'];
            //$file_name=$dataArray['title'];
            $maxDownloadSize=256 * 1024 * 128;
            $endDataFixed=$maxDownloadSize-1;
            while($fileSize-$startData>=$maxDownloadSize)
            {
                $range="${startData}" . "-" . "${endDataFixed}";
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RANGE,$range);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                
                file_put_contents("${file_name}",$response,FILE_APPEND);
                $startData=$startData+$maxDownloadSize;
                $endDataFixed=$endDataFixed+$maxDownloadSize;
            }
            if($fileSize-$startData==0)
            {
                return $file_name;
            }
            else if($fileSize-$startData<$maxDownloadSize)
            {
                $endDataFixed=$fileSize-1;
                $range="${startData}" . "-" . "${endDataFixed}";
                $uri="https://www.googleapis.com/drive/v3/files/${fileId}?alt=media";
                $curl_resource=curl_init();
                curl_setopt($curl_resource,CURLOPT_URL,$uri);
                curl_setopt($curl_resource,CURLOPT_HTTPGET,TRUE);
                curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                 "Authorization: Bearer ${token}",
                ));
                curl_setopt($curl_resource,CURLOPT_RANGE,$range);
                curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
                $response=curl_exec($curl_resource);
                curl_close($curl_resource);
                
                //$myfile = fopen("${file_name}","a+");
                file_put_contents("${file_name}",$response,FILE_APPEND);
                $startData=$startData+$maxDownloadSize;
                $endDataFixed=$endDataFixed+$maxDownloadSize;
                return $file_name;
            }
        }
        public static function getMetadataAPI($fileId,$username)
        {
            $token = self::getTokenAPI($username);
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
        public static function getSizeFileAPI($fileId,$username)
        {
           
            $response=self::getMetadataAPI($fileId,$username);
            $responseArray=json_decode($response,true);
            return $responseArray['fileSize'];
        }




}



    
?>