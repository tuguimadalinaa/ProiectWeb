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
        $response = Login::getApprovalFromDB($_REQUEST['username'],$_REQUEST['password']);
        if(empty($_SESSION["loggedIn"])){
            $json_response = json_decode($response,true);
            if($json_response['status']=='0'){
                Login::StartSession();
                echo $response;
            }else{
                echo $response;
            }
        }else{
            echo $response;
        }
    }
});
Route::set('signUp', function(){
    Login::CreateView('signUp');
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
});
Route::set('getCode', function(){
    $drive_type = $_REQUEST['drive'];
    if($drive_type =='OneDrive'){
        $response = OneDrive::GetCode();
    echo $response;
    }
});
Route::set('getToken',function(){
    if(empty($_REQUEST['code'])){
        echo "No code";
    }else{
        $drive_type = $_REQUEST['drive'];
        if($drive_type =='OneDrive'){
            $response = OneDrive::GetToken($_REQUEST['code']);
            echo $response;
        }
    }
});
?>