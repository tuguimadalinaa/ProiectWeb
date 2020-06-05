<?php
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
                    'expires' => time() + 86400,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                Login::Cookie("Dropbox","root",[
                    'expires' => time() + 86400,
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
        Home::Createview('index');
    }
});
Route::set('about',function(){
    Home::CreateView('about');
});


Route::set('Dropbox_files',function(){
    Home::CreateView('Dropbox_files');
});

Route::set('OneDrive_files',function(){
    Home::CreateView('OneDrive_files');
});

Route::set('GoogleDrive_files',function(){
    Home::CreateView('GoogleDrive_files');
});


Route::set('logOut',function(){
    //Login::EndSession();
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
    //header('Location: home');   //Robert, musai trebuie 
    echo 'Logout';
});
Route::set('getCode', function(){
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
});

Route::set('getToken',function(){
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
});

/* --------------------------------------------- GoogleDrive --------------------------------------------- */

Route::set('createFolderGoogleDrive',function(){
    $username=(Controller::getAuth()->jwtDecode($_COOKIE['loggedIn']))->username;
    $access_token_json = Controller::getModel()->getAccessToken($username,'GoogleDrive');
    $access_token_decoded = json_decode($access_token_json,true);
    $access_token = $access_token_decoded['access_token'];
    //echo $access_token;
    if($access_token != null){
        $response = GoogleDrive::createFolder($_REQUEST['fileName']);
        echo $response;
  } else {
      header('Location: getCode?drive=GoogleDrive');
  }
});

Route::set('uploadGoogleDrive',function()
{
    $username=(Controller::getAuth()->jwtDecode($_COOKIE['loggedIn']))->username;
    $access_token_json = Controller::getModel()->getAccessToken($username,'GoogleDrive');
    $access_token_decoded = json_decode($access_token_json,true);
    $access_token = $access_token_decoded['access_token'];
    //echo $access_token;
    if($access_token != null){
        $response = GoogleDrive::uploadFileResumable();
        echo $response;
  } else {
      header('Location: getCode?drive=GoogleDrive');
  }
});
Route::set('listGoogleDrive',function(){
    $username=(Controller::getAuth()->jwtDecode($_COOKIE['loggedIn']))->username;
    $access_token_json = Controller::getModel()->getAccessToken($username,'GoogleDrive');
    $access_token_decoded = json_decode($access_token_json,true);
    $access_token = $access_token_decoded['access_token'];
    //echo $access_token;
    if($access_token != null){
        $response = GoogleDrive::listAllFiles();
        echo $response;
  } else {
      header('Location: getCode?drive=GoogleDrive');
  }
});

Route::set('deleteFileGoogleDrive',function(){
    $response=GoogleDrive::deleteFile($_REQUEST['folderId']);
    echo $response;
});
Route::set('getMetadataFileGoogleDrive',function(){
     $response=GoogleDrive::getMetadata($_REQUEST['fileId']);
     echo $response;
});

Route::set('downloadFileGoogleDrive',function(){
    $response=GoogleDrive::downloadSimpleFile($_REQUEST['fileId']);
    $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $response;
    $file_name = basename($file_to_download);
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=${file_name}");
    header("Content-Length: " . filesize($file_to_download));
    readfile($file_to_download);
    unlink($file_to_download);
});

/* --------------------------------------------- Dropbox --------------------------------------------- */

Route::set('uploadDropbox',function(){   
    $username=(Controller::getAuth()->jwtDecode($_COOKIE['loggedIn']))->username;
    $access_token_json = Controller::getModel()->getAccessToken($username,'Dropbox');
    $access_token_decoded = json_decode($access_token_json,true);
    $access_token = $access_token_decoded['access_token'];
    if($access_token != null){
          $response = Dropbox::uploadFile();
          echo $response;
    } else {
        header('Location: getCode?drive=DropBox');
    }
});

Route::set('deleteItemDropbox',function(){
    $deleted_item_id = $_REQUEST['item_id'];
    Dropbox::deleteItem($deleted_item_id);
    echo 'Item deleted';
});

Route::set('changeFolderDropbox',function(){
    $changed_folder_id = $_REQUEST['folder_id'];
    Dropbox::Cookie("Dropbox",$changed_folder_id,[
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    echo 'Cookie Folder value changed';
 });

Route::set('moveItemDropbox',function(){
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
});

Route::set('uploadSmallFile',function(){
    $headers = apache_request_headers();
    $file_path_json = 'Unknown';
    foreach ($headers as $header => $value) {
        if($header == 'File-Args'){
            $file_path_json = $value;
            break;
        }
    }
    $file_path_array = json_decode($file_path_json,true);
    $requestBody = file_get_contents('php://input');
    $response = Dropbox::uploadSmallFile($requestBody,$file_path_array);
    echo 'File uploaded';
});

Route::set('uploadLargeFileStart',function(){
    $entityBody = file_get_contents('php://input');
    echo $entityBody;
});

Route::set('uploadLargeFileAppend',function(){
    $entityBody = file_get_contents('php://input');
    echo $entityBody;
});

Route::set('uploadLargeFileFinish',function(){
    $entityBody = file_get_contents('php://input');
    echo $entityBody;
});



Route::set('getFolderFilesDropbox',function(){ 
   $response = DropBox::getFolderFiles($_COOKIE['Dropbox']);
   echo $response;
});

Route::set('previousFolderDropbox',function(){
    $parent_id = Dropbox::getParentFolderId($_COOKIE["Dropbox"]);
    Dropbox::Cookie("Dropbox",$parent_id,[
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    echo 'Previous Folder';
});

Route::set('downloadFileDropbox',function(){ 
    $downloaded_item_id = $_REQUEST['file_id'];
    $response = Dropbox::downloadFileByLink($downloaded_item_id);
    header("Location: ${response}");
});

Route::set('downloadFolderDropbox',function(){
 $folder_id = $_REQUEST['folder_id'];
 $response = Dropbox::downloadFolder($folder_id);
 $file_to_download = $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $response . '.zip';
 $file_name = basename($file_to_download);
 header("Content-Type: application/zip");
 header("Content-Disposition: attachment; filename=${file_name}");
 header("Content-Length: " . filesize($file_to_download));
 readfile($file_to_download);
 unlink($file_to_download);
});


Route::set('createFolderDropbox',function(){ //Ruta testing
    $response = DropBox::createFolder($_COOKIE['Dropbox'],$_REQUEST['folder_name']);
    echo $response;
});

Route::set('renameItemDropbox',function(){
   $response = Dropbox::renameItem($_REQUEST['item_id'],$_REQUEST['new_name']);
   echo $response;
});

Route::set('downloadFileDropbox',function(){ //Ruta testing
       Dropbox::download();
       //Dropbox::downloadByLink();
});

Route::set('getFileMetadataDropbox',function(){ //Ruta testing
    Dropbox::getFileMetadata();
});

/* --------------------------------------------- OneDrive --------------------------------------------- */

Route::set('transferFile',function(){
    if(!empty(file_get_contents('php://input')) && !empty($_REQUEST['fileTransfName'])&& !empty($_REQUEST['fileSize'])){
        $response = OneDrive::UploadFile($_REQUEST['fileTransfName'],file_get_contents('php://input'),$_REQUEST['fileSize']);
        echo $response;
    }else{
        echo json_encode(array("status"=>'1'));
    }
});
Route::set('transferBigFile',function(){
    if((!empty(file_get_contents('php://input')) && !empty($_REQUEST['fileTransfName'])&& !empty($_REQUEST['fileSize'])&&!empty($_REQUEST['readyToGo']))
        ||$_REQUEST['readyToGo']=="true"){
        $response = OneDrive::UploadBigFile($_REQUEST['fileTransfName'],file_get_contents('php://input'),$_REQUEST['fileSize'],$_REQUEST['readyToGo']);
        echo $response;
    }
    else{
        echo json_encode(array("status"=>'1'));
    }
});
Route::set('registrationConfirmed',function(){
    ConfirmedRegistration::Createview('registrationConfirmed');
});
Route::set('getFile',function(){
    if($_REQUEST['type']=='file')
    {
        echo OneDrive::GetFile($_REQUEST['fileTransfName']);
    }
    else{
        echo OneDrive::downloadDirectory($_REQUEST['fileTransfName']);
    }
});
Route::set('getDirectoryOneDrive', function(){
    echo OneDrive::ListAllFiles($_REQUEST['name']);
});
Route::set('deleteFile',function(){
    echo OneDrive::deleteFile($_REQUEST['fileTransfName']);
});
Route::set('createFolder',function(){
    if(!empty($_REQUEST['fileTransfName']) && !empty($_REQUEST['path'])){
        $response = OneDrive::createFolder($_REQUEST['fileTransfName'],$_REQUEST['path']);
        echo $response;
    }
    else{
        echo json_encode(array("status"=>'1'));
    }
    
});
//https://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
?>