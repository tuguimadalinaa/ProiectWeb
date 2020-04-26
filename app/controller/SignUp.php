<?php
class SignUp extends Controller{
    public static function createAccount($userName, $password){
        return self::getModel()->addUser($userName, $password);
    }
}
?>