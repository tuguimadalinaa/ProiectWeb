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

    public static function getFileForDownload($file_name,$username,$googledriveId){
       $file_exists = 0;
       $file_in_googledrive=GoogleDrive::checkIfFileExistGoogleDrive($file_name,$username,$googledriveId);
       if($file_in_googledrive==1)
       {
        $fileSize=GoogleDrive::getSizeFileAPI($googledriveId,$username);
        if($fileSize<=256 * 1024 * 128)
        {
            $responseGoogleDrive = GoogleDrive::downloadSmallFilesAPI($googledriveId,$username,$file_name);
            $file_exists=1;
        }
        else{
            $responseGoogleDrive = GoogleDrive::downloadLargeFilesAPI($googledriveId,$username,$file_name);
            $file_exists=1;
        }
       }
       $file_in_onedrive  = OneDrive::checkFileExists('/drive/root:/2'.$username.$file_name,$username);
       if($file_in_onedrive=="true")
       {
            $content = OneDrive::contentDownload('/2'.$username.$file_name,$username);
            $file = file_put_contents($file_name,$content,FILE_APPEND);
            $file_exists = 1;
       }
       $file_in_dropbox = Dropbox::checkIfFileExist($file_name,$username);
       if($file_in_dropbox == 1){
         $responseDropbox = Dropbox::downloadFileAPI($file_name,$username);
         $file_exists = 1;
       }
       if($file_exists == 1){
           $file_downloaded =  $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $file_name;
           return $file_downloaded;
       } else {
           return '0';
       }
    }

    public static function fileFragmentation($file_name,$username){
        $dropbox_space_left = Dropbox::getStorage($username);
        $googledrive_space_left = GoogleDrive::getStorageQuota($username);
        $onedrive_space_left = OneDrive::getStorage($username);
        $file_to_upload =  $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $file_name;
        $file_size = filesize($file_to_upload);
        $total_size = $file_size;
        if($file_size <= ($dropbox_space_left + $googledrive_space_left + $onedrive_space_left)){
            $googledrive_size = floor($total_size/3);
            $onedrive_size = floor($total_size/3);
            $dropbox_size = $total_size - $googledrive_size - $onedrive_size;
            if($googledrive_size > $googledrive_space_left){
                $googledrive_size = $googledrive_space_left;
                $total_size = $total_size - $googledrive_size;
                $onedrive_size = floor($total_size/2);
                $dropbox_size = floor($total_size/2);
                if($onedrive_size > $onedrive_space_left){
                    $onedrive_size = $onedrive_space_left;
                    $total_size = $total_size - $onedrive_size;
                    $dropbox_size = $total_size;
                }
                if($dropbox_size > $dropbox_space_left){
                    $dropbox_size = $dropbox_space_left;
                    $total_size = $total_size - $dropbox_size;
                    $onedrive_size = $total_size;
                }
            } else if($onedrive_size > $onedrive_space_left){
                $onedrive_size = $onedrive_space_left;
                $total_size = $total_size - $onedrive_size;
                $googledrive_size = floor($total_size/2);
                $dropbox_size = floor($total_size/2);
                if($googledrive_size > $googledrive_space_left){
                    $googledrive_size = $googledrive_space_left;
                    $total_size = $total_size - $googledrive_size;
                    $dropbox_size = $total_size;
                }
                if($dropbox_size > $dropbox_space_left){
                    $dropbox_size = $dropbox_space_left;
                    $total_size = $total_size - $dropbox_size;
                    $googledrive_size = $total_size;
                }
            } else if($dropbox_size > $dropbox_space_left){
                $dropbox_size = $dropbox_space_left;
                $total_size = $total_size - $dropbox_size;
                $googledrive_size = floor($total_size/2);
                $onedrive_size = floor($total_size/2);
                if($googledrive_size > $googledrive_space_left){
                    $googledrive_size = $googledrive_space_left;
                    $total_size = $total_size - $googledrive_size;
                    $onedrive_size = $total_size;
                }
                if($onedrive_size > $onedrive_space_left){
                    $onedrive_size = $onedrive_space_left;
                    $total_size = $total_size - $onedrive_size;
                    $googledrive_size = $total_size;
                }
            }
            $offset = 0;
            $googledrive_file_name="1".$file_name;
            $googledrive_data=file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
            if($googledrive_size<=256 * 1024 * 128)//32 mb
            {
                $googledrive_id=GoogleDrive::uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username);
            }
            else
            {
                $googledrive_id=GoogleDrive::uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username);
            }
            $offset = $offset + $googledrive_size;
            $onedrive_filename = "/2".$file_name;
            $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
            $onedrive_response = OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
            $offset = $offset + $onedrive_size;
            $dropbox_data = file_get_contents($file_name,FALSE,null,$offset,$dropbox_size);
            $dropbox_file_name = "3" . $file_name;
            if($dropbox_size <= 1048576 * 40){
                 Dropbox::uploadSmallFileAPI($dropbox_data,$dropbox_file_name,$username);
            } else {
                 Dropbox::uploadLargeFileAPI($dropbox_data,$dropbox_file_name,$username);
            }
            unlink($file_to_upload);
            return $googledrive_id;
        } else {
            return 'No space for upload';
        }

    }

    public static function fileFragmentationDEMO($file_name,$username){
       $file_to_upload =  $_SERVER['DOCUMENT_ROOT'] . '/ProiectWeb/app/' . $file_name;
       $file_size = filesize($file_to_upload);
       $googledrive_size = floor($file_size / 3);
       $onedrive_size = floor($file_size / 3);
       $dropbox_size=$file_size-$googledrive_size-$onedrive_size;
       $offset = 0;
       $googledrive_file_name="1".$file_name;
       $googledrive_data=file_get_contents($file_name,FALSE,null,$offset,$googledrive_size);
       if($googledrive_size<=256 * 1024 * 128)//32 mb
       {
           $googledrive_id=GoogleDrive::uploadSmallFileAPI($googledrive_data,$googledrive_file_name,$username);
           //return "BUBU";
       }
       else
       {
        $googledrive_id=GoogleDrive::uploadLargeFileAPI($googledrive_data,$googledrive_file_name,$username);
        //return $response;
       }

       $offset = $offset + $googledrive_size;
       $onedrive_filename = "/2".$file_name;
       $onedrive_data = file_get_contents($file_name,FALSE,null,$offset,$onedrive_size);
       OneDrive::UploadFileAPI($onedrive_filename,$onedrive_data,$onedrive_size,$username);
       $offset=$offset+$onedrive_size;

       $dropbox_data = file_get_contents($file_name,FALSE,null,$offset,$dropbox_size);
       $dropbox_file_name = "3" . $file_name;
       if($dropbox_size <= 1048576 * 40){
            Dropbox::uploadSmallFileAPI($dropbox_data,$dropbox_file_name,$username);
       } else {
            Dropbox::uploadLargeFileAPI($dropbox_data,$dropbox_file_name,$username);
       }
       unlink($file_to_upload);
       return $googledrive_id;
    }
    

}
?>