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
            Controller::CreateView('login');
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
Route::set('transferFile',function(){
    $response = $_REQUEST['fileData'];
    echo $response;
});
?>