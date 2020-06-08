<?php
class SessionHandlerModel{
    public static function StartSession(){
        if(!isset($_SESSION)) 
        { 
            session_start(); 
        } 
        if(empty($_SESSION["loggedIn"]))
            $_SESSION["loggedIn"] = "true";
    }
    public static function EndSession(){
        session_destroy();
    }
}
?>