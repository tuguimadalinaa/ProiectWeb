<?php
Route::set('login',function(){
    if(empty($_REQUEST['username'])){
        if(!empty($_SESSION['loggedIn'])){
            header('Location: home');
        }else{
            Login::CreateView('login');
        }
    }
    else{
        if(!empty($_SESSION["loggedIn"])){
            header('Location: home');
        }
        $response = Login::getApprovalFromDB($_REQUEST['username'],$_REQUEST['password']);
        if(empty($_SESSION["loggedIn"])){
            $json_response = json_decode($response,true);
            if($json_response['status']=='0'){
                Login::StartSession();
                echo $response;
            }else if($json_response['status']=='1' ||$json_response['status']=='2'){
                echo $response;
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
Route::set('home',function(){
    if(empty($_SESSION['loggedIn'])){
        header('Location: login');    
    }else{
        Home::Createview('index');
    }
});
Route::set('about',function(){
    Home::CreateView('about');
});
Route::set('your-files',function(){
    Home::CreateView('fileRender');
});
Route::set('logOut',function(){
    Login::EndSession();
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
        } else if($drive_type ='DropBox'){
            $response = DropBox::GetToken($_REQUEST['code']);
            echo $response;
        }
        else{
            $respone=GoogleDrive::GetToken($_REQUEST['code']);
            echo $response;
        }
    }
});

Route::set('uploadDropbox',function(){   //Ruta testing
    Dropbox::uploadFile();
});

Route::set('getFilesDropbox',function(){ //Ruta testing
   DropBox::getFolderFiles();
});

Route::set('createFolderDropbox',function(){ //Ruta testing
    DropBox::createFolder();
});

Route::set('transferFile',function(){
    if(!empty(file_get_contents('php://input')) && !empty($_REQUEST['fileTransfName'])&& !empty($_REQUEST['fileSize'])){
        $response = OneDrive::UploadFile($_REQUEST['fileTransfName'],file_get_contents('php://input'),$_REQUEST['fileSize']);
        echo $response;
    }else{
        echo json_encode(array("status"=>'1'));
    }
});
Route::set('registrationConfirmed',function(){
    ConfirmedRegistration::Createview('registrationConfirmed');
});
//https://stackoverflow.com/questions/8945879/how-to-get-body-of-a-post-in-php
?>