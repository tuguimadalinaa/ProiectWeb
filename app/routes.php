<?php
ini_set('max_execution_time',240);
Route::set('login',function(){
    if(empty($_REQUEST['username'])){
        if(isset($_COOKIE["loggedIn"])){
            header('Location: home');
        }else{
            Login::CreateView('login');
        }
    }
    else{
        if(isset($_COOKIE["loggedIn"])){
            header('Location: home');
        }
        $data = Login::getApprovalFromDB($_REQUEST['username'],$_REQUEST['password']);
        if(!isset($_COOKIE["loggedIn"])){
            $json_response = json_decode($data,true);
            if($json_response['status']==0){
                Login::Cookie("loggedIn",$json_response['jwt'],[
                    'expires' => time() + 3600*24*7,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                Login::Cookie("Dropbox","root",[
                    'expires' => time() + 3600*24*7,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                Login::Cookie("GoogleDrive","root",[
                    'expires' => time() + 3600*24*7,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                Login::Cookie("OneDrive","/drive/root",[
                    'expires' => time() + 3600*24*7,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo $data;
            }else if($json_response['status']==1 ||$json_response['status']==2){
                echo $data;
            }
        }
    }
});

Route::set('signUp', function(){
    if(empty($_REQUEST['username']) && empty($_REQUEST['password'])){
         Controller::CreateView('signUp');
    } else{
        $response = SignUp::createAccount($_REQUEST['username'],$_REQUEST['password']);
        $status = json_decode($response,true);
        if($status['status'] == '1'){
            echo $response;
        } else {
            Controller::CreateView('signUp');
        }
    }
});

Route::set('registrationConfirmed',function(){
    ConfirmedRegistration::Createview('registrationConfirmed');
});

Route::set('home',function(){
    if(!isset($_COOKIE["loggedIn"])){
        header('Location: login');    
    }else{
        $response_jwt_validation = Login::validateJwtCookie();
        if($response_jwt_validation == 'JWT valid'){
            Home::CreateView('index');
        } else {
           http_response_code(401);
           echo 'Invalid JWT';
           header('Location: logOut');
        } 
    }
});

Route::set('about',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        Home::CreateView('about');
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('Dropbox_files',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        Home::CreateView('Dropbox_files');
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('OneDrive_files',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        Home::CreateView('OneDrive_files');
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('GoogleDrive_files',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        Home::CreateView('GoogleDrive_files');
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('logOut',function(){
    //Login::EndSession();
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $username = (Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        Login::getModel()->userLogOut($username);
        Login::Cookie("loggedIn","JWToken",[
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        Login::Cookie("Dropbox","root",[
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        Login::Cookie("GoogleDrive","root",[
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        Login::Cookie("OneDrive","/drive/root:/Documents",[
            'expires' => time() -3600,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        Login::CreateView('login');
        echo 'Logout';
    } else {
        echo 'Invalid JWT';
    }
});
Route::set('getCode', function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $drive_type = $_REQUEST['drive'];
        if($drive_type =='OneDrive'){
            $response = OneDrive::GetCode();
        } else if($drive_type == 'DropBox'){
            $response = Dropbox::GetCode();
        }
        else{
            $response= GoogleDrive::GetCode();
        }
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('getToken',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        if(empty($_REQUEST['code'])){
            echo "No code";
        }else{
            $drive_type = $_REQUEST['drive'];
            if($drive_type =='OneDrive'){
                $response = OneDrive::GetToken($_REQUEST['code']);
                echo $response;
            } else if($drive_type =='DropBox'){
                $response = DropBox::GetToken($_REQUEST['code']);
                echo $response;
            }
            else{
                $response=GoogleDrive::GetToken($_REQUEST['code'],$_COOKIE["loggedIn"]);
                echo $response;
            }
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});


/* --------------------------------------------- GoogleDrive --------------------------------------------- */

Route::set('createFolderGoogleDrive',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response = GoogleDrive::createFolder($_REQUEST['fileName'],$_COOKIE["GoogleDrive"]);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?drive=GoogleDrive');
        }
     }
    else {
        http_response_code(401);
        echo 'Invalid JWT';
        header('Location: logOut');
    }
});
Route::set('obtainUriForUploadGoogleDrive',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0){
            $response = GoogleDrive::obtainUriForResumable($_REQUEST['fileName'],$_COOKIE['GoogleDrive']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?drive=GoogleDrive');
        }
     } 
    else {
        http_response_code(401);
        echo 'Invalid JWT';
        header('Location: logOut');
    };
});
Route::set('getStorageQuotaGoogleDrive',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0){
         $response=GoogleDrive::getStorageQuota();
         echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('uploadSmallFilesGoogleDrive',function()
{
    $response_jwt_validation = Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $headers= apache_request_headers();
            $fileLink="Unknown";
            foreach ($headers as $header => $value) {
                if($header == 'File-Link'){
                    $fileLink = $value;
                    break;
                }
            }
             $fileLinkArray=json_decode($fileLink,true);
             $fileBody=file_get_contents('php://input');
             $response = GoogleDrive::uploadSmallFileResumableGoogleDrive($fileLinkArray["linkusor"],$fileBody);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('uploadLargeFilesGoogleDrive',function(){

    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $headers= apache_request_headers();
            $fileLink="Unknown";
            foreach ($headers as $header => $value) {
            if($header == 'File-Link'){
            $fileLink = $value;
            break;
                }
            }
            $fileLinkArray=json_decode($fileLink,true);
            $fileBody=file_get_contents('php://input');
            $response = GoogleDrive::uploadLargeFileResumable($fileLinkArray["linkusor"],$fileBody,$fileLinkArray["Start"],$fileLinkArray["End"],$fileLinkArray["SizeFile"]);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('listGoogleDrive',function(){

$response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response = GoogleDrive::listAllFiles($_COOKIE['GoogleDrive']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('deleteFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::deleteFile($_REQUEST['fileId']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('changeFolderGoogleDrive',function(){
    $changed_folder_id = $_REQUEST['fileId'];
    GoogleDrive::Cookie("GoogleDrive",$changed_folder_id,[
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    echo 'Cookie Folder value changed';
 });
Route::set('previousFolderGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $parent_id = GoogleDrive::getParentFolderId($_COOKIE["GoogleDrive"]);
            GoogleDrive::Cookie("GoogleDrive",$parent_id,[
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        echo 'Previous Folder';
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('getMetadataFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::getMetadata($_REQUEST['fileId']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('getFileNameGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::getNameFile($_REQUEST['fileId']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('getFileSizeGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::getSizeFile($_REQUEST['fileId']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('getFolderParentGoogleDrive',function()
{
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::getParentFolderId($_COOKIE["GoogleDrive"]);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('renameFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::renameFile($_REQUEST['fileName'],$_REQUEST['fileId']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});

Route::set('downloadSmallFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::downloadSmallFile($_REQUEST['fileId']);
            $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $response;
            $file_name = basename($file_to_download);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=". $file_name);
            header("Content-Length: " . filesize($file_to_download));
            readfile($file_to_download);
            unlink($file_to_download);
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('downloadLargeFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::downloadLargeFile($_REQUEST['fileId']);
            $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $response;
            $file_name = basename($file_to_download);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=". $file_name);
            header("Content-Length: " . filesize($file_to_download));
            readfile($file_to_download);
            unlink($file_to_download);
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('moveFileGoogleDrive',function(){
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            if(isset($_COOKIE["GoogleDrive-MV"])){
                if($_REQUEST['fileId'] == '0'){
                $response = GoogleDrive::moveFile($_COOKIE['GoogleDrive-MV'],$_COOKIE['GoogleDrive']);
                GoogleDrive::Cookie("GoogleDrive-MV",'invalid',[
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo 'File moved';
            } else{
                GoogleDrive::Cookie("GoogleDrive-MV",$_REQUEST['fileId'],[
                    'expires' => time() + 86400,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo 'Item stored in cookie';
            }
        }   else{
                if($_REQUEST['fileId'] != '0'){
                    GoogleDrive::Cookie("GoogleDrive-MV",$_REQUEST['fileId'],[
                        'expires' => time() + 86400,
                        'path' => '/',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Strict',
                    ]);
                    echo 'File stored in cookie';
                } else {
                    echo 'Not a valid file id';
                }
            }
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
Route::set('getIdByName',function()
{
    $response_jwt_validation=Login::validateJwtCookie();
    $response_token_time=GoogleDrive::getInfoToken();
    if($response_jwt_validation == 'JWT valid'){
        if($response_token_time!=0)
        {
            $response=GoogleDrive::getMetadaByName($_REQUEST['fileName']);
            echo $response;
        }
        else
        {
            http_response_code(401);
            echo 'Invalid token';
            header('Location: getCode?	drive=GoogleDrive');
        }
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
});
/* --------------------------------------------- Dropbox --------------------------------------------- */

//Pentru validarea jwt-ul la fiecare ruta

/*
 $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
         
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    } 
 */

Route::set('deleteItemDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $deleted_item_id = $_REQUEST['item_id'];
        Dropbox::deleteItem($deleted_item_id);
        echo 'Item deleted';
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('changeFolderDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $changed_folder_id = $_REQUEST['folder_id'];
        Dropbox::Cookie("Dropbox",$changed_folder_id,[
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        echo 'Cookie Folder value changed';
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
 });

Route::set('moveItemDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        if(isset($_COOKIE["Dropbox-MV"])){
            if($_REQUEST['item_id'] == '0'){
                $response = Dropbox::moveItem($_COOKIE['Dropbox'],$_COOKIE['Dropbox-MV']);
                Dropbox::Cookie("Dropbox-MV",'invalid',[
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo 'Item moved';
            } else {
                Dropbox::Cookie("Dropbox-MV",$_REQUEST['item_id'],[
                    'expires' => time() + 86400,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo 'Item stored in cookie';
            }
            
        } else{
            if($_REQUEST['item_id'] != '0'){
                Dropbox::Cookie("Dropbox-MV",$_REQUEST['item_id'],[
                    'expires' => time() + 86400,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
                echo 'Item stored in cookie';
            } else {
                echo 'Not a valid item id';
            }
        }
    } else {
       http_response_code(401); 
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('uploadSmallFileDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $headers = apache_request_headers();
        $file_path_json = null;
        foreach ($headers as $header => $value) {
            if($header == 'File-Args'){
                $file_path_json = $value;
                break;
            }
        }
        if($file_path_json != null){
            $file_path_array = json_decode($file_path_json,true);
            $requestBody = file_get_contents('php://input');
            $response = Dropbox::uploadSmallFile($requestBody,$file_path_array);
            echo 'File uploaded';
        } else {
            echo "Can't find header Session-Args ";
        }
        
    } else {
       http_response_code(401); 
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('uploadLargeFileStartDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $requestBody = file_get_contents('php://input');
        $folder_id_file = fopen("folder_id.txt", "w");
        $bytesWritten = fwrite($folder_id_file,$_COOKIE['Dropbox']); 
        $response = Dropbox::uploadSessionStart($requestBody);
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('uploadLargeFileAppendDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $headers = apache_request_headers();
        $cursor_id = 'Unknown';
        $offset = 'Unknown';
        $decoded_json_value = null;
        foreach ($headers as $header => $value) {
            if($header == 'Session-Args'){
                $decoded_json_value = json_decode($value,true);
                break;
            }
        }
        if($decoded_json_value != null){
            $cursor_id = $decoded_json_value['cursorId'];
            $offset = $decoded_json_value['offset'];
            $requestBody = file_get_contents('php://input');
            $response = Dropbox::uploadSessionAppend($requestBody,$cursor_id,$offset);
            echo $response;
        } else {
            echo "Can't find header Session-Args ";
        }
       
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('uploadLargeFileFinishDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $headers = apache_request_headers();
        $cursor_id = 'Unknown';
        $offset = 'Unknown';
        $file_name = 'Unknown';
        $decoded_json_value = null;
        foreach ($headers as $header => $value) {
            if($header == 'Session-Args'){
                $decoded_json_value = json_decode($value,true);
                break;
            }
        }
        if($decoded_json_value != null){
            $cursor_id = $decoded_json_value['cursorId'];
            $offset = $decoded_json_value['offset'];
            $file_name = $decoded_json_value['name'];
            $requestBody = file_get_contents('php://input');
            $parent_id = file_get_contents("folder_id.txt");
            $response = Dropbox::uploadSessionFinish($requestBody,$cursor_id,$offset,$file_name,$parent_id);
            unlink("folder_id.txt");
            echo $response;
        } else {
            echo "Can't find header Session-Args ";
        }
    } else {
       http_response_code(401);  
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});



Route::set('getFolderFilesDropbox',function(){ 
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $response = DropBox::getFolderFiles($_COOKIE['Dropbox']);
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('previousFolderDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $parent_id = Dropbox::getParentFolderId($_COOKIE["Dropbox"]);
        Dropbox::Cookie("Dropbox",$parent_id,[
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        echo 'Previous Folder';
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('downloadFileDropbox',function(){ 
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $downloaded_item_id = $_REQUEST['file_id'];
        $response = Dropbox::downloadFileByLink($downloaded_item_id);
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('downloadFolderDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $folder_id = $_REQUEST['folder_id'];
        $response = Dropbox::downloadFolder($folder_id);
        $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $response . '.zip';
        $file_name = basename($file_to_download);
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=${file_name}");
        header("Content-Length: " . filesize($file_to_download));
        readfile($file_to_download);
        unlink($file_to_download);
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});


Route::set('createFolderDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $response = DropBox::createFolder($_COOKIE['Dropbox'],$_REQUEST['folder_name']);
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
});

Route::set('renameItemDropbox',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $response = Dropbox::renameItem($_REQUEST['item_id'],$_REQUEST['new_name']);
        echo $response;
    } else {
       http_response_code(401);
       echo 'Invalid JWT';
       header('Location: logOut');
    }
   
});


/* --------------------------------------------- OneDrive --------------------------------------------- */

Route::set('transferFile',function(){
    if(!empty(file_get_contents('php://input')) && !empty($_REQUEST['fileTransfName'])&& !empty($_REQUEST['fileSize'])){
        $response_jwt_validation = Login::validateJwtCookie();
        if($response_jwt_validation == 'JWT valid')
        {
            $response = OneDrive::UploadFile($_REQUEST['fileTransfName'],file_get_contents('php://input'),$_REQUEST['fileSize']);
            echo $response;
        }else{
            echo json_encode(array("status"=>'1'));
        }
        
    }else{
        echo json_encode(array("status"=>'1'));
    }
});
Route::set('transferBigFile',function(){
    
    $response_jwt_validation = Login::validateJwtCookie();
    if((!empty(file_get_contents('php://input')) && !empty($_REQUEST['fileTransfName'])&& !empty($_REQUEST['fileSize'])&&!empty($_REQUEST['readyToGo']))
        ||$_REQUEST['readyToGo']=="true"){
            if($response_jwt_validation == 'JWT valid')
            {
                $response = OneDrive::UploadBigFile($_REQUEST['fileTransfName'],file_get_contents('php://input'),$_REQUEST['fileSize'],$_REQUEST['readyToGo']);
                echo $response;
            }else{
                echo json_encode(array("status"=>'1'));
            }
        
    }
    else{
        echo json_encode(array("status"=>'1'));
    }
});
Route::set('registrationConfirmed',function(){
    ConfirmedRegistration::Createview('registrationConfirmed');
});
Route::set('getFile',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($_REQUEST['type']=='file')
    {
        if($response_jwt_validation == 'JWT valid'){
            echo OneDrive::GetFile($_REQUEST['fileTransfName']);
        }
        else{
            echo json_encode(array("status"=>'1'));
        }
    }
    else{
        echo OneDrive::downloadDirectory($_REQUEST['fileTransfName']);
    }
});
Route::set('getDirectoryOneDrive', function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        echo OneDrive::ListAllFiles($_REQUEST['name']);
    }else{
        echo json_encode(array("status"=>'1'));
    }
    
});
Route::set('deleteFile',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid')
    {
        echo OneDrive::deleteFile($_REQUEST['fileTransfName']);
    }else{
        echo json_encode(array("status"=>'1'));
    }
    
});
Route::set('createFolder',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if(!empty($_REQUEST['fileTransfName']) && !empty($_REQUEST['path'])){
        if($response_jwt_validation == 'JWT valid'){
            $response = OneDrive::createFolder($_REQUEST['fileTransfName'],$_REQUEST['path']);
            echo $response;
        }else{
            echo json_encode(array("status"=>'1'));
        }
       
    }
    else{
        echo json_encode(array("status"=>'1'));
    }
    
});
Route::set('renameFolder',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if(!empty($_REQUEST['fileTransfName'])  && !empty($_REQUEST['oldName'])){
        if($response_jwt_validation == 'JWT valid')
        {
            $response = OneDrive::renameFolder($_REQUEST['fileTransfName'],$_REQUEST['oldName']);
            echo $response;
        }else{
            echo json_encode(array("status"=>'1'));
        }
        
    }
    else{
        echo json_encode(array("status"=>'1'));
    }
    
});
Route::set('goBack',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        echo json_encode(array("status"=>$_COOKIE['OneDrive']));
    }else{
        echo json_encode(array("status"=>'401'));
    }
   
    
});
Route::set('moveFile',function(){
    //echo OneDrive::moveFile($_REQUEST['newPath'],$_REQUEST['fileTransfName']);
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        echo OneDrive::moveFile($_REQUEST['newPath'],$_REQUEST['fileTransfName']);
    }
    else{
        echo json_encode(array("status"=>'401'));
    }
    
});
Route::set('uploadLargeStart',function(){
    //echo OneDrive::moveFile($_REQUEST['newPath'],$_REQUEST['fileTransfName']);
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        
        $response = OneDrive::StartSessionUpload($_REQUEST['fileTransfName']);
        echo $response;
    }
    else{
        echo json_encode(array("status"=>'401'));
    }
    
});
Route::set('uploadLargeFileAppend',function(){
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $requestBody = file_get_contents('php://input');
        $headers = apache_request_headers();
        $cursor_id = 'Unknown';
        $offset = 'Unknown';
        $url  = 'Unknown';
        $decoded_json_value = 0;
        
        foreach ($headers as $header => $value) {
            if($header == 'Session-Args'){
                $decoded_json_value = json_decode($value,true);
                break;
            }
        } 
        $offset = $decoded_json_value['offset'];
        $url = $decoded_json_value['url'];
        $totalSize = $decoded_json_value['totalSize'];
        $last_range = $decoded_json_value['lastRange'];
        echo OneDrive::AppendFile($requestBody,$url,$offset,$totalSize,$last_range);
    }
    else{
        echo json_encode(array("status"=>'401'));
    }
    
});
Route::set('uploadLargeFileFinish',function(){
    //echo OneDrive::moveFile($_REQUEST['newPath'],$_REQUEST['fileTransfName']);
    $response_jwt_validation = Login::validateJwtCookie();
    if($response_jwt_validation == 'JWT valid'){
        $requestBody = file_get_contents('php://input');
        $headers = apache_request_headers();
        $cursor_id = 'Unknown';
        $offset = 'Unknown';
        $url  = 'Unknown';
        $decoded_json_value = 0;
        
        foreach ($headers as $header => $value) {
            if($header == 'Session-Args'){
                $decoded_json_value = json_decode($value,true);
                break;
            }
        } 
        $offset = $decoded_json_value['offset'];
        $url = $decoded_json_value['url'];
        $totalSize = $decoded_json_value['totalSize'];
        $last_range = $decoded_json_value['lastRange'];
        echo OneDrive::FinishFile($requestBody,$url,$offset,$totalSize,$last_range);
    }
    else{
        echo json_encode(array("status"=>'401'));
    }
    
});
Route::set('contentDownload',function(){
    echo OneDrive::contentDownload($_REQUEST['fileNameTransf']);
});
//

/* ---------------------------------------- API routes general ---------------------------------------- */

//Pattern pentru validarea JWT folosind API-ul


 /*    $headers = apache_request_headers();
     $responseJWTheader = Login::validateJwtRequest($headers);
     if($responseJWTheader == 'JWT valid'){

     } else {
     http_response_code(401);
     $error = array("error" => "invalid JWT");
     header('Content-Type: application/json');
     echo json_encode($error);
     }
*/

Route::set('APIcheckJWT',function(){ //Ruta testing
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    if($responseJWTheader == 'JWT valid'){
       echo 'E bun';
    } else {
       echo 'Nu e bun';
    }
});

Route::set('APIregisterUser',function(){
    $requestBody = json_decode(file_get_contents('php://input'),true);
    if($requestBody != null){
        if(count($requestBody) == 2){
            if(array_key_exists('username',$requestBody) && array_key_exists('password',$requestBody)){
                if($requestBody['username'] != null && $requestBody['password'] != null){
                    $response = SignUp::createAccount($requestBody['username'],$requestBody['password']);
                    $status = json_decode($response,true);
                    if($status['status'] == '1'){
                        $data = Login::getApprovalFromDB($requestBody['username'],$requestBody['password']);
                        $json_response = json_decode($data,true);
                        $jwt = $json_response['jwt'];
                        http_response_code(200);
                        $response = array("JWT" => $jwt);
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else if($status['status'] == '2') {
                        http_response_code(500);
                        $error = array("error" => "Server error, please try again");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    } else if($status['status'] == '3'){
                        http_response_code(400);
                        $error = array("error" => "Username already exists");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    } else {
                        http_response_code(500);
                        $error = array("error" => "Server error, please try again");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
    
                } else {
                    http_response_code(400);
                    $error = array("error" => "Missing username or password field value(or both)");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing username or password field(or both)");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Invalid number of fields in body");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else {
        http_response_code(400);
        $error = array("error" => "Malformed json");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
    
});

Route::set('APIrefreshJWT',function(){
    $requestBody = json_decode(file_get_contents('php://input'),true);
    if($requestBody != null){
        if(count($requestBody) == 2){
            if(array_key_exists('username',$requestBody) && array_key_exists('password',$requestBody)){
                if($requestBody['username'] != null && $requestBody['password'] != null){
                    $data = Login::getApprovalFromDB($requestBody['username'],$requestBody['password']);
                    $json_response = json_decode($data,true);
                    $jwt = $json_response['jwt'];
                    if($jwt != ""){
                        http_response_code(200);
                        $response = array("JWT" => $jwt);
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else {
                        http_response_code(404);
                        $error = array("error" => "Invalid username or password(or both)");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
                } else {
                    http_response_code(400);
                    $error = array("error" => "Missing username or password field value(or both)");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing username or password field(or both)");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Invalid number of fields in body");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else {
        http_response_code(400);
        $error = array("error" => "Malformed json");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
    
});
Route::Set('APIhome1',function(){
    if(isset($_GET['code'])){
        $code = $_GET['code'];
        if(isset($_GET['scope'])){
          $scope = $_GET['scope'];
          http_response_code(200);
          $response = array("code" => "${code}", "scope" => "${scope}");
          header('Content-Type: application/json');
          echo json_encode($response);
        } else {
          http_response_code(200);
          $response = array("code" => "${code}", "scope" => "null");
          header('Content-Type: application/json');
          echo json_encode($response);
        }
    } else {
      http_response_code(500);
      $error = array("error" => "No code");
      header('Content-Type: application/json');
      echo json_encode($error);
    }
});

Route::set('APIgetCode',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    if($responseJWTheader == 'JWT valid'){
        $api_args = null;
        foreach ($headers as $header => $value) {
            if($header == 'Api-Args'){
                $api_args = $value;
                break;
            }
        }
       if($api_args != null){
           $decoded_api_args = json_decode($value,true);
           $drive = $decoded_api_args['drive'];
           $drive_response = 0;
           if($drive == 'OneDrive'){
              $drive_response = OneDrive::GetCodeAPI();
              http_response_code(200);
              $response = array("url" => "${drive_response}");
              header('Content-Type: application/json');
              echo json_encode($response);  
            
           } else if($drive == 'Dropbox'){
              $drive_response = Dropbox::APIGetCode();
              http_response_code(200);
              $response = array("url" => "${drive_response}");
              header('Content-Type: application/json');
              echo json_encode($response);
           } else if($drive == 'GoogleDrive'){
              $drive_response = GoogleDrive::APIGetCode();
              http_response_code(200);
              $response = array("url" => "${drive_response}");
              header('Content-Type: application/json');
              echo json_encode($response);
           } else {
            http_response_code(400);
            $error = array("error" => "Invalid Api-Args");
            header('Content-Type: application/json');
            echo json_encode($error);
           }
       } else {
           http_response_code(400);
           $error = array("error" => "Missing Api-Args");
           header('Content-Type: application/json');
           echo json_encode($error);
       }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('APIuploadStart',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    $file_args = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'File-Args'){
                $file_args = json_decode($value,true);
            }
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($file_args != null){
            $file_name = $file_args['name'];
            if($jwt != null){
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
                echo 'Tuto bene header';
            } else {
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
                echo 'Tuto bene cookie';
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Header File-Args invalid");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'Cookie not found') {
        http_response_code(400);
        $error = array("error" => "Cookie with jwt not found");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('APIuploadAppend',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    $file_args = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'File-Args'){
                $file_args = json_decode($value,true);
            }
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($file_args != null){
            $file_name = $file_args['name'];
            if($jwt != null){
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
                //echo 'Tuto bene header';
            } else {
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
               // echo 'Tuto bene cookie';
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Header File-Args invalid");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'Cookie not found') {
        http_response_code(400);
        $error = array("error" => "Cookie with jwt not found");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('APIuploadFinish',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    $file_args = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'File-Args'){
                $file_args = json_decode($value,true);
            }
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($file_args != null){
            $file_name = $file_args['name'];
            $drive = $file_args['drive'];
            if($jwt != null){
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
            } else {
                $requestBody = file_get_contents('php://input');
                $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                $file_name_custom = $username . $file_name;
                $file = file_put_contents($file_name_custom,$requestBody,FILE_APPEND);
            }
            if($drive == 'Dropbox'){
                $dropbox_size = filesize($file_name_custom);
                $dropbox_data = file_get_contents($file_name_custom);
                $dropbox_file_name = $file_name;
                if($dropbox_size <= 1048576 * 40){
                    Dropbox::uploadSmallFileAPI($dropbox_data,$dropbox_file_name,$username);
                } else {
                    Dropbox::uploadLargeFileAPI($dropbox_data,$dropbox_file_name,$username);
                }    
                return 'Uploaded Dropbox';
            } else if($drive == 'OneDrive'){
                $onedrive_size = filesize($file_name_custom);
                $onedrive_data = file_get_contents($file_name_custom);
                $onedrive_filename = $file_name;
                $onedrive_response = OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
                return 'Uploaded OneDrive';
            } else if($drive == 'GoogleDrive'){
                $googledrive_file_name = $file_name;
                $googledrive_data = file_get_contents($file_name_custom);
                $googledrive_size = filesize($file_name_custom);
                if($googledrive_size<=256 * 1024 * 128)//32 mb
                {
                   $googledrive_id=GoogleDrive::uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username);
                }
                else
                {
                   $googledrive_id=GoogleDrive::uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username);
                }
            } else if($drive == 'AllDrives'){
                $response = Fragmentation::fileFragmentation($file_name_custom,$username);
                if($response != 'No space for upload'){
                    http_response_code(200);
                    $response = array("response" => "File ${file_name} uploaded","googledrive_id" => "${response}");
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } else {
                    http_response_code(507);
                    $error = array("error" => "No space left for this file");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Drive value invalid");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Header File-Args invalid");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'Cookie not found') {
        http_response_code(400);
        $error = array("error" => "Cookie with jwt not found");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('APIregisterToken',function(){ 
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    if($responseJWTheader == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $requestBody = json_decode(file_get_contents('php://input'),true);
        if($requestBody != null){
            if(count($requestBody) == 3){
                if(array_key_exists('code',$requestBody) && array_key_exists('drive',$requestBody) && array_key_exists('scope',$requestBody)){
                    if($requestBody['code'] != null && $requestBody['drive'] != null && $requestBody['scope'] != null){
                        if($requestBody['drive'] == 'OneDrive'){

                            $response = OneDrive::GetTokenApi($requestBody['code'],$jwt);
                            http_response_code(200);
                            $response = array("response" => $response);
                            header('Content-Type: application/json');
                            echo json_encode($response); 

                        } else if($requestBody['drive'] == 'Dropbox'){
                            $response = Dropbox::APIgetToken($requestBody['code'],$jwt);
                            // http_response_code(200);
                            // $responseJson = array("response" => "${response}");
                            // header('Content-Type: application/json');
                            // echo json_encode($responseJson);
                            if($response == 'Access Granted'){
                                http_response_code(200);
                                $responseJson = array("response" => "${response}");
                                header('Content-Type: application/json');
                                echo json_encode($responseJson);
                            } else if($response == 'Null token'){
                                http_response_code(400);
                                $error = array("error" => "Null access token");
                                header('Content-Type: application/json');
                                echo json_encode($error);
                            } else {
                                http_response_code(400);
                                $error = array("error" => "Invalid code");
                                header('Content-Type: application/json');
                                echo json_encode($error);
                            } 
                        } else if($requestBody['drive'] == 'GoogleDrive'){
                            $response = GoogleDrive::APIgetToken($requestBody['code'],$jwt);
                            if($response == 'Access Granted'){
                                http_response_code(200);
                                $responseJson = array("response" => "${response}");
                                header('Content-Type: application/json');
                                echo json_encode($responseJson);
                            } else {
                                http_response_code(400);
                                $error = array("error" => "Invalid code");
                                header('Content-Type: application/json');
                                echo json_encode($error);
                            }
                        } else {
                            http_response_code(400);
                            $error = array("error" => "Invalid drive name");
                            header('Content-Type: application/json');
                            echo json_encode($error);
                        }
                    } else {
                        http_response_code(400);
                        $error = array("error" => "Missing code or drive or scope value(or all of them)");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
                } else {
                    http_response_code(400);
                    $error = array("error" => "Missing code or drive or scope field(or all of them)");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Invalid number of fields in body");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Malformed json");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('APIdownloadFile',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $requestBody = json_decode(file_get_contents('php://input'),true);
        if($requestBody != null){
            if($requestBody['name'] != null && $requestBody['googledrive_id'] != null){
                if($jwt != null){
                    $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                } else {
                    $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                }
                $file_name = $requestBody['name'];
                $googledrive_id = $requestBody['googledrive_id'];
                $result = Fragmentation::getFileForDownload($file_name,$username,$googledrive_id);
                if($result != '0'){
                    $file_to_download = $result;
                    $file_name = basename($file_to_download);
                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=${file_name}");
                    header("Content-Length: " . filesize($file_to_download));
                    readfile($file_to_download);
                    unlink($file_to_download);
                } else {
                   http_response_code(400);
                   $error = array("error" => "Missing name or googledrive_id field(or both)");
                   header('Content-Type: application/json');
                   echo json_encode($error); 
                }
            } else {
                http_response_code(410);
                $error = array("error" => "Missing name or googledrive_id field(or both)");
                header('Content-Type: application/json');
                echo json_encode($error); 
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Missing or malformed json");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

/* ---------------------------------------- API routes Dropbox ---------------------------------------- */

Route::set('getTotalRemainingSpace',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        $remaining_space_google = GoogleDrive::getStorageQuota($username);
        $remaining_space_dropbox = Dropbox::getStorage($username);
        $remaining_space_onedrive = OneDrive::getStorage($username);
        $remaining_space_total = $remaining_space_dropbox + $remaining_space_google + $remaining_space_onedrive;
        http_response_code(200);
        $responseJson = array("space_left" => "${remaining_space_total}");
        header('Content-Type: application/json');
        echo json_encode($responseJson);
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('getRemainingSpaceGoogleDrive',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        $remaining_space = GoogleDrive::getStorageQuota($username);
        http_response_code(200);
        $responseJson = array("space_left" => "${remaining_space}");
        header('Content-Type: application/json');
        echo json_encode($responseJson);
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('getRemainingSpaceOneDrive',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        $remaining_space = OneDrive::getStorage($username);
        http_response_code(200);
        $responseJson = array("space_left" => "${remaining_space}");
        header('Content-Type: application/json');
        echo json_encode($responseJson);
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('getRemainingSpaceDropbox',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        $remaining_space = Dropbox::getStorage($username);
        http_response_code(200);
        $responseJson = array("space_left" => "${remaining_space}");
        header('Content-Type: application/json');
        echo json_encode($responseJson);
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

/* --------------------------------------------- Admin API Routes -------------------------------------------- */

Route::set('adminGetNumberOfLoggedUsers',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($jwt != null){
            $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        } else {
            $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        }
        $response = Controller::getModel()->adminGetNumberOfUsersLogged($username);
        if($response != 'Forbidden Access'){
            http_response_code(200);
            $response = array("number_of_logged_users" => "${response}");
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(401);
            $error = array("error" => "${response}");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminGetLoggedUsers',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($jwt != null){
            $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        } else {
            $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        }
        $response = Controller::getModel()->adminGetLoggedUsers($username);
        if($response != 'Forbidden Access'){
            http_response_code(200);
            $responseArray = json_encode($response);
            $response = array("users" => "${responseArray}");
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(401);
            $error = array("error" => "${response}");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminDeleteUser',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        $requestBody = json_decode(file_get_contents('php://input'),true);
        if($requestBody != null){
            if($requestBody['username_to_delete'] != null){
                $username_to_delete = $requestBody['username_to_delete'];
                if($jwt != null){
                    $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                } else {
                    $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                }
                $response = Controller::getModel()->adminDeleteUser($username,$username_to_delete);
                if($response == "User deleted"){
                    http_response_code(200);
                    $response = array("response" => "${response}");
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } else {
                    http_response_code(400);
                    $error = array("error" => "${response}");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing username_to_delete field value");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Missing or malformed body");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminEditUser',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        $requestBody = json_decode(file_get_contents('php://input'),true);
        if($requestBody != null){
            if($requestBody['username'] != null && $requestBody['new_username'] != null && $requestBody['password'] != null && $requestBody['logged'] != null && $requestBody['OneDrive_access_token'] != null && $requestBody['Dropbox_access_token'] != null && $requestBody['GoogleDrive_access_token'] != null && $requestBody['registration_date'] != null && $requestBody['last_logged_in'] != null ){
                    if($jwt != null){
                        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                    } else {
                        $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                    }
                    $response = Controller::getModel()->adminEditUser($username,$requestBody);
                    if($response == 'User added'){
                        http_response_code(200);
                        $response = array("response" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else {
                        http_response_code(400);
                        $error = array("error" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing some body fields");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Missing or malformed body");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminAddUser',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        $requestBody = json_decode(file_get_contents('php://input'),true);
        if($requestBody != null){
            if($requestBody['username'] != null && $requestBody['password'] != null){
                    if($jwt != null){
                        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                    } else {
                        $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                    }
                    $response = Controller::getModel()->adminAddUser($username,$requestBody);
                    if($response == 'User added'){
                        http_response_code(200);
                        $response = array("response" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else {
                        http_response_code(400);
                        $error = array("error" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing username or passowrd field value(or both)");
                header('Content-Type: application/json');
                echo json_encode($error);
            }
        } else {
            http_response_code(400);
            $error = array("error" => "Missing or malformed body");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
        
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminImportDatabase',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    $api_args_value = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
            if($header == 'Api-Args'){
                $api_args_value = $value;
            }
        }
        if($api_args_value != null){
            if($api_args_value == "Append"){
                $requestBody = file_get_contents('php://input');
                $csv_imported_file = file_put_contents('database_imported.csv',$requestBody);
                if($requestBody != null){
                    if($jwt != null){
                        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                    } else {
                        $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                    }
                    $response = Controller::getModel()->importDataCSV($username,"Append");
                    if($response == 'Data imported'){
                        http_response_code(200);
                        $responseArray = json_encode($response);
                        $response = array("response" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else {
                        http_response_code(401);
                        $error = array("error" => "${response}");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
                } else {
                    http_response_code(400);
                    $error = array("error" => "Missing body data");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else if($api_args_value == "Overwrite"){
                $requestBody = file_get_contents('php://input');
                $csv_imported_file = file_put_contents('database_imported.csv',$requestBody);
                if($requestBody != null){
                    if($jwt != null){
                        $username=(Controller::getAuth()->jwtDecode($jwt))->username;
                    } else {
                        $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
                    }
                    
                    $response = Controller::getModel()->importDataCSV($username,"Overwrite");
                    if($response == 'Data imported'){
                        http_response_code(200);
                        $responseArray = json_encode($response);
                        $response = array("response" => "${responseArray}");
                        header('Content-Type: application/json');
                        echo json_encode($response);
                    } else {
                        http_response_code(401);
                        $responseArray = json_encode($response);
                        $error = array("error" => "${responseArray}");
                        header('Content-Type: application/json');
                        echo json_encode($error);
                    }
                } else {
                    http_response_code(400);
                    $error = array("error" => "Missing body data");
                    header('Content-Type: application/json');
                    echo json_encode($error);
                }
            } else {
                http_response_code(400);
                $error = array("error" => "Missing Api-Args value or invalid");
                header('Content-Type: application/json');
                echo json_encode($error);
            } 
        } else {
            http_response_code(400);
            $error = array("error" => "Missing Api-Args header ");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});

Route::set('adminExportDatabase',function(){
    $headers = apache_request_headers();
    $responseJWTheader = Login::validateJwtRequest($headers);
    $responseJWTcookie = Login::validateJwtCookie();
    $jwt = null;
    if($responseJWTheader == 'JWT valid' || $responseJWTcookie == 'JWT valid'){
        foreach($headers as $header => $value){
            if($header == 'Auth'){
                $jwt = $value;
            }
        }
        if($jwt != null){
            $username=(Controller::getAuth()->jwtDecode($jwt))->username;
        } else {
            $username=(Controller::getAuth()->jwtDecode($_COOKIE["loggedIn"]))->username;
        }
        $response = Controller::getModel()->exportDataCSV($username);
        if($response == 'Data exported'){
            $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/database_exported.csv' ;
            $file_name = basename($file_to_download);
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename=${file_name}");
            header("Content-Length: " . filesize($file_to_download));
            readfile($file_to_download);
            //unlink($file_to_download);
        } else {
            http_response_code(401);
            $error = array("error" => "${response}");
            header('Content-Type: application/json');
            echo json_encode($error);
        }
    } else if($responseJWTheader == 'JWT invalid'){
        http_response_code(401);
        $error = array("error" => "Expired or invalid JWT");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'JWT is empty'){
        http_response_code(400);
        $error = array("error" => "Missing JWT value");
        header('Content-Type: application/json');
        echo json_encode($error);
    } else if($responseJWTheader == 'No Auth Header') {
        http_response_code(400);
        $error = array("error" => "Missing JWT header");
        header('Content-Type: application/json');
        echo json_encode($error);
    }
});


?>
