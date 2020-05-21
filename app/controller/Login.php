<?php
class Login extends Controller{
    public static function getApprovalFromDB($username,$password){
        return self::getModel()->getApprovalForLogin($username,$password);
    }
    public static function StartSession(){
        return self::getSessionHandler()->StartSession();
    }
    public static function EndSession(){
        return self::getSessionHandler()->EndSession();
    }
    public static function Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path){
        return self::getCookieHandler()->Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path);
    }
}
?>