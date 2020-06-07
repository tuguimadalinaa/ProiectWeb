<?php
/*include 'C:\xampp\htdocs\ProiectWeb\app\models\auth_jwt.php';*/
include '.\models\auth_jwt.php';
ini_set('max_execution_time',240);
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

    public static function getFileForDownload($file_name,$username){
       $file_exists = 0;
       $file_in_dropbox = Dropbox::checkIfFileExist($file_name,$username);
       if($file_in_dropbox == 1){
         $responseDropbox = Dropbox::downloadFileAPI($file_name,$username);
         $file_exists = 1;
       }
       if($file_exists == 0){
           return '0';
       }
       $file_in_onedrive  = OneDrive::checkFileExists('/drive/root:/Documents/2'.$username.$file_name,$username);
       if($file_in_onedrive=="true")
       {
            $content = OneDrive::contentDownload('Documents/2'.$username.$file_name,$username);
            $file = file_put_contents($file_name,$content,FILE_APPEND);
            return $file_name;
       }
       else if($file_in_onedrive=="false")
       {
            return '0';
       }
    }

    public static function fileFragmentation($file_name,$username){
       $file_to_upload =  $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $file_name;
       $file_size = filesize($file_to_upload);
       if($file_size % 2 == 0){
         $dropbox_size = $file_size / 2;
         $onedrive_size = $file_size - $dropbox_size;
       } else {
         $dropbox_size = $file_size / 2 + 0.5;
         $onedrive_size = $file_size - $dropbox_size;
       }
       $offset = 0;
       $dropbox_data = file_get_contents($file_name,FALSE,null,$offset,$dropbox_size);
       $dropbox_file_name = "1" . $file_name;
       if($dropbox_size <= 1048576 * 40){
            Dropbox::uploadSmallFileAPI($dropbox_data,$dropbox_file_name,$username);
       } else {
            Dropbox::uploadLargeFileAPI($dropbox_data,$dropbox_file_name,$username);
       }
       $offset = $offset + $dropbox_size;
       $onedrive_filename = "2".$file_name;
       $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
       return  OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
       
    }
    

}
?>