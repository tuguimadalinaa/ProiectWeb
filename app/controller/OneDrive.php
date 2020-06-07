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
    public static function GetCodeAPI(){
        $data=[
            'client_id' => self::$client_id,
            'client_secret'=> self::$client_secret,
            'scope'=>'offline_access Files.ReadWrite.All',
            'redirect_uri' => "http://localhost/ProiectWeb/app/APIhome1",
            'response_type' =>'code',
        ];
        $query_string=http_build_query($data);
        return self::$url_code.'?'.$query_string;
    }
    public static function GetTokenAPI($code,$jwt){
        $data=[
            'client_id' => self::$client_id,
            'scope'=>'offline_access Files.ReadWrite.All',
            'code' => $code,
            'redirect_uri' => "http://localhost/ProiectWeb/app/APIhome1",
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
                $username=(self::getAuth()->jwtDecode($jwt))->username;
                self::getModel()->addAccessToken($access_token,$username,'OneDrive');
                return $access_token;
            }
        }
        catch(Exception $e){
            return $e;
            //return  json_encode(array("status"=>'401'));
        }
        
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
            return $e;
            //return  json_encode(array("status"=>'401'));
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
        $fileName = str_replace ( ' ', '%20', $fileName );
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
        $fileName = str_replace ( ' ', '%20', $fileName );
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
        $fileName = str_replace ( ' ', '%20', $fileName );
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me'.$fileName, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
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
    public static function makeRequestForListFiles($access_token,$fileNameToRender)
    {
        
        $create_curl=curl_init();
        if($fileNameToRender=="/drive/root:/")
        {
            $fileNameToRender="/drive/root";
        }
        if($fileNameToRender=="/drive/root" || $fileNameToRender=='\/drive\/root')
        {
            
            curl_setopt_array($create_curl,[
                CURLOPT_URL=>'https://graph.microsoft.com/v1.0//me'.$fileNameToRender.'/children',
                CURLOPT_RETURNTRANSFER=>1,
                CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
                CURLOPT_SSL_VERIFYPEER=>false
            ]); 
        }
        else{
            $fileNameToRender = str_replace ( ' ', '%20', $fileNameToRender );
            curl_setopt_array($create_curl,[
                CURLOPT_URL=>'https://graph.microsoft.com/v1.0//me'.$fileNameToRender.':/children',
                CURLOPT_RETURNTRANSFER=>1,
                CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
                CURLOPT_SSL_VERIFYPEER=>false
            ]); 
        }
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        $decodedResponse = json_decode($response, true);
        if($fileNameToRender!='/drive/root' || $fileNameToRender!='\/drive\/root')
        {
            $cookie_params_array = [
                'expires' => time() + 8600,
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict',
            ];
            $splittedExplode  = explode('/',$decodedResponse['value'][0]["parentReference"]["path"]);
            $end = end($splittedExplode);
            $split = explode($end,$decodedResponse['value'][0]["parentReference"]["path"]);
            self::getCookieHandler()->Cookie('OneDrive',$split[0],$cookie_params_array);
        }
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
        $fileName = str_replace ( ' ', '%20', $fileName);
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
    public static function createFolderRequest($fileName,$path,$access_token)
    {
        $path= str_replace ( ' ', '%20', $path);
        $folder = '{'.$path.'}';
        $data=[
            "name"=>$fileName,
            "folder"=>["childCount" => '0'],
            "@microsoft.graph.conflictBehavior"=>"rename"
        ];
        $query_string=http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/'.$path.':/children',
            CURLOPT_USERAGENT => 'STOL2',
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array("Authorization: Bearer ".$access_token,
            "Content-Type: application/json"),
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);
        $response=curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public static function createFolder($fileName,$path)
    {
        //de pus in cookie altfel nu merge daca e gol
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response  = self::createFolderRequest($fileName,$path,$access_token);
        $decodedResponse = json_decode($response, true);
        if(isset($decodedResponse['@odata.context']))
        {
            return json_encode(array("status"=>'200'));
        }
        else{
            return json_encode(array("status"=>'401'));
        }
    }
    public static function updateFolderRequest($newFileName,$access_token,$id)
    {
        $data= json_encode(array("name"=>$newFileName));
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/items/'.$id, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_CUSTOMREQUEST=>'PATCH',
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                        "Content-Type: application/json"),
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POSTFIELDS=>$data
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function renameFolder($newFileName,$fileName)
    {
        $fileName = str_replace ( ' ', '%20', $fileName);
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response  = self::makeRequestForFile($access_token,$fileName);
        $decodedResponse = json_decode($response, true);
        $id = $decodedResponse['id'];
        $response  = self::updateFolderRequest($newFileName,$access_token,$id);
        if(isset($decodedResponse['@odata.context']))
        {
            return json_encode(array("status"=>'200'));
        }
        else{
            return json_encode(array("status"=>'401'));
        }
    }
    public static function makeRequestMoveFile($access_token,$fileName,$newPath)
    {
        $response  = self::makeRequestForFile($access_token,$newPath);
        $decodedResponse = json_decode($response, true);
        if(!isset($decodedResponse['id']))
        {
            return json_encode(array("status"=>"Path given doesn't exist"));
        }
        $idNewPath = $decodedResponse['id'];
        $response  = self::makeRequestForFile($access_token,$fileName);
        $decodedResponse = json_decode($response, true);
        $idOldPath = $decodedResponse['id'];
        $parentReference  = json_encode(array("id"=>$idNewPath));
        $parameters = json_encode(array("parentReference"=>$parentReference,"name"=>"FolderNou"));
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/items/'.$idOldPath, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_CUSTOMREQUEST=>'PATCH',
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                        "Content-Type: application/json"),
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POSTFIELDS=>$parameters
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        //return json_encode(array("acc"=>$access_token,"raspuns"=>$response));
        return $response;
    }
    public static function moveFile($newPath,$fileName)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response  = self::makeRequestMoveFile($access_token,$fileName,$newPath);
        return $response;
    }
    
    public static function StartSessionUpload($fileName)
    {
        $fileName = str_replace ( ' ', '%20', $fileName);
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $data= json_encode(array('item'=>array(
            '@microsoft.graph.conflictBehavior'=>'rename',"@odata.type"=> "microsoft.graph.driveItemUploadableProperties","name"=>$fileName)
        )); 
        if(strcmp($access_token,"fail")!=0){
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
            $decode = json_decode($response,true);
            $urlToUpload  = $decode['uploadUrl'];
            return json_encode(array("status"=>'200',"response"=>$urlToUpload));
        }
        return json_encode(array("status"=>'401'));
    }
    
    private static function WriteFileBig($fileData,$access_token,$fileSize,$graph_url,$totalSize,$last_range){
        $content_length = $fileSize-$last_range;
        $upload_curl=curl_init();
        curl_setopt_array($upload_curl,[
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_URL=>$graph_url,
            CURLOPT_CUSTOMREQUEST=>'PUT',
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                "Content-Length: ${content_length}",
                                'Content-Range: bytes '.$last_range."-".($fileSize-1).'/'.$totalSize
                            ),
            CURLOPT_POSTFIELDS=>$fileData,
            CURLOPT_BINARYTRANSFER => TRUE
        ]);
        $response=curl_exec($upload_curl);
        curl_close($upload_curl);
        return $response;
    }
    public static function Appendfile($requestBody,$url,$fileSize,$totalSize,$last_range)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response = self::WriteFileBig($requestBody,$access_token,$fileSize,$url,$totalSize,$last_range);
        return $response;
    }
    private static function WriteFileFinish($fileData,$access_token,$fileSize,$graph_url,$totalSize,$last_range){
        
        $content_length = $totalSize - $fileSize;
        $upload_curl=curl_init();
        curl_setopt_array($upload_curl,[
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_URL=>$graph_url,
            CURLOPT_CUSTOMREQUEST=>'PUT',
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                "Content-Length: ${content_length}",
                                'Content-Range: bytes '.$last_range."-".($totalSize-1).'/'.$totalSize
                            ),
            CURLOPT_POSTFIELDS=>$fileData,
            CURLOPT_BINARYTRANSFER => TRUE
        ]);
        $response=curl_exec($upload_curl);
        curl_close($upload_curl);
        return $response;
    }
    public static function Finishfile($requestBody,$url,$fileSize,$totalSize,$last_range)
    {
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $response = self::WriteFileFinish($requestBody,$access_token,$fileSize,$url,$totalSize,$last_range);
        return $response;
    }
    public static function downloadByContent($fileName,$urlForDownload,$size,$access_token)
    {
        $size=$size-1;
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>$urlForDownload,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}",
                                        "Range: bytes=0-${size}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        return $response;
    }
    public static function contentDownload($fileName)
    {
        $fileName = str_replace ( ' ', '%20', $fileName);
        $username=(self::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        $create_curl=curl_init();
        curl_setopt_array($create_curl,[
            CURLOPT_URL=>'https://graph.microsoft.com/v1.0/me/drive/root:/'.$fileName, //spatiile in url dau erori
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_HTTPHEADER=>array("Authorization: Bearer ${access_token}"),
            CURLOPT_SSL_VERIFYPEER=>false
        ]); 
        $response=curl_exec($create_curl);
        curl_close($create_curl);
        $decodedResponse = json_decode($response, true);
        $urlForDownload = $decodedResponse['@microsoft.graph.downloadUrl'];
        $size = $decodedResponse['size'];
        $response  = self::downloadByContent($fileName,$urlForDownload, $size, $access_token);
        return $response;

    }
    private static function WriteFileAPI($fileName,$fileData,$access_token,$fileSize,$graph_url){
        $fileName = str_replace ( ' ', '%20', $fileName );
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
    public static function UploadFileAPI($fileName, $fileData,$fileSize,$username){
        $access_token = self::getAccesTokenFromDB($username,'OneDrive');
        if(strcmp($access_token,"fail")!=0){
            $response = self::createFile($fileName,$access_token);
            $json_response = json_decode($response,true);
            $graph_url = $json_response['uploadUrl'];
            $response = self::WriteFileAPI($fileName,$fileData,$access_token,$fileSize,$graph_url);
            $decodedResponse = json_decode($response, true);
            $fileId = $decodedResponse['id'];
            return json_encode(array("status"=>'200',"id"=>$fileId));
        }
        return json_encode(array("status"=>'401'));
    }
}
?>