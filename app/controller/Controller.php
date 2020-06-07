<?php
/*include 'C:\xampp\htdocs\ProiectWeb\app\models\auth_jwt.php';*/
include '.\models\auth_jwt.php';
class Controller{
    public static function getModel(){
        $model = new DataBase();
        return $model;
    }
    public static function CreateView($viewName){
       require_once("../views/HTML/$viewName.html");
    }
    public static function getSessionHandler(){
        $session = new SessionHandlerModel();
        return $session;
    }
    public static function getCookieHandler(){
        $cookie_handler=new CookieHandlerModel();
        return $cookie_handler;
    }
    public static function getAuth(){
        $auth=new Auth();
        return $auth;
    }

    public static function fileFragmentation($file_name,$jwt){
       $file_to_upload =  $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $file_name;
       $file_size = filesize($file_to_upload);
       if($file_size % 2 == 0){
        $dropbox_size = $file_size / 2;
        $onedrive_size = $file_size / 2;
       } else {
        $dropbox_size = $file_size / 2;
        $onedrive_size = $file_size / 2 + 1;
       }
       $offset = 0;
       $dropbox_data = file_get_contents($file_name,FALSE,null,$offset,$dropbox_size);
       $offset = $dropbox_size;
       $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
       $offset = $onedrive_size;
    //    $file_to_put_togheter = 'PutTogheter' . $file_name;
    //    $my_file = file_put_contents($file_to_put_togheter,$dropbox_data,FILE_APPEND);
    //    $my_fule = file_put_contents($file_to_put_togheter,$onedrive_data,FILE_APPEND);
    //    $googledrive_data = file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
    //    $offset = $googledrive_size;
    }


}
?>