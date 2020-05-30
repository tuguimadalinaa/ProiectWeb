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
                Login::Cookie("loggedIn",$json_response['jwt'],time() + 36000,'/',null,FALSE,TRUE);
                Login::Cookie("Dropbox","root",time() + 36000,'/',null,FALSE,FALSE);
                //Login::StartSession();
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
    Login::Cookie("loggedIn","JWToken",time() - 3600,'/',null,FALSE,TRUE);
    Login::Cookie("Dropbox","root",time() - 3600,'/',null,FALSE,FALSE);
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
     $response=GoogleDrive::getMetadata();
     echo $response;
});

// Route::set('downloadFolderGoogleDrive',function(){
//     $response=GoogleDrive::exportFolders();
//     echo $response;
// });

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
    Dropbox::Cookie("Dropbox",$changed_folder_id,time() + 36000,'/',null,FALSE,FALSE);
    echo 'Cookie Folder value changed';
 });

Route::set('getFolderFilesDropbox',function(){ 
   $response = DropBox::getFolderFiles($_REQUEST['folder_id']);
   echo $response;
});

Route::set('previousFolderDropbox',function(){
    $parent_id = Dropbox::getParentFolderId($_COOKIE["Dropbox"]);
    //echo $_COOKIE['Dropbox'];
    //$parent_id = Dropbox::getParentFolderId('id:Z9Jcd_nPTfAAAAAAAAAAIQ');
    Dropbox::Cookie("Dropbox",$parent_id,time() + 36000,'/',null,FALSE,FALSE);
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
    DropBox::createFolder();
});

Route::set('uploadDropboxSession',function(){ //Ruta testing
        Dropbox::uploadSessionStart();
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
//https://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
?>