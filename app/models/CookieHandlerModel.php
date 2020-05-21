<?php
class CookieHandlerModel{
    public static function Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path){
        setcookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path);
    }
}
?>