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
            Login::StartSession();
        }
        echo $response;
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
    $response = OneDrive::GetCode();
    echo $response;
});
Route::set('getToken',function(){
    if(empty($_REQUEST['code'])){
        echo "No code";
    }else{
        $response = OneDrive::GetToken($_REQUEST['code']);
        echo $response;
    }
});
?>