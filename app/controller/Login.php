<?php
class Login extends Controller{
    public static function getApprovalFromDB($username,$password){
        return self::getModel()->getApprovalForLogin($username,$password);
    }
    public static function StartSession(){
        return self::getSessionHandler()->StartSession();
    }
    public static function EndSession(){
        self::getSessionHandler()->EndSession();
    }
}
?>