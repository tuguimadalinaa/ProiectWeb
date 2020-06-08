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

    public static function getFileForDownload($file_name,$username){
       $file_exists = 0;
       $file_in_googledrive=GoogleDrive::checkIfFileExist($file_name,$username);
       if($file_in_googledrive==1)
       {
        $responseDropbox = GoogleDrive::downloadFileAPI($file_name,$username);
        $file_exists=1;
       }
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
       $googledrive_size = floor($file_size / 3);
       $onedrive_size = floor($file_size / 3);
       $dropbox_size=$file_size-$googledrive_size-$onedrive_size;
       $offset = 0;
       $googledrive_file_name="3".$file_name;
       $googledrive_data=file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
       if($googledrive_size<=256 * 1024 * 128)//32 mb
       {
           GoogleDrive::uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username);
           //return "BUBU";
       }
       else
       {
        $response=GoogleDrive::uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username);
        return $response;
       }

       $offset = $offset + $googledrive_size;
       $onedrive_filename = "2".$file_name;
       $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
       $nebunie=OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
       $offset=$offset+$onedrive_size;

       $dropbox_data = file_get_contents($file_name,FALSE,null,$offset,$dropbox_size);
       $dropbox_file_name = "1" . $file_name;
       if($dropbox_size <= 1048576 * 40){
            Dropbox::uploadSmallFileAPI($dropbox_data,$dropbox_file_name,$username);
       } else {
            Dropbox::uploadLargeFileAPI($dropbox_data,$dropbox_file_name,$username);
       }
      //return $response;
    //    $offset = $offset + $dropbox_size;
    //    $onedrive_filename = "2".$file_name;
    //    $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
    //    $nebunie=OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
    //    $offset=$offset+$onedrive_size;
    //    $googledrive_file_name="3".$file_name;
    //    $googledrive_data=file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
    //    if($googledrive_size<=256 * 1024 * 128)//32 mb
    //    {
    //        GoogleDrive::uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username);
    //        return "BUBU";
    //    }
    //    else
    //    {
    //     $response=GoogleDrive::uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username);
    //     return $response;
    //    }
       //return $dropbox_size . " bam " . $onedrive_size . " bum " . $googledrive_size . " juju " . $nebunie;
    //    $file_to_put_togheter = 'PutTogheter' . $file_name;
    //    $my_file = file_put_contents($file_to_put_togheter,$dropbox_data,FILE_APPEND);
    //    $my_fule = file_put_contents($file_to_put_togheter,$onedrive_data,FILE_APPEND);
    //    $googledrive_data = file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
    //    $offset = $googledrive_size;
    }
    

}
?>