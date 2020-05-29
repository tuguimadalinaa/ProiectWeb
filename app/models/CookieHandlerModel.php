<?php
class CookieHandlerModel{
    public static function Cookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path,$cookie_domain,$cookie_secure,$cookie_httponly){
        setcookie($cookie_name,$cookie_value,$cookie_expiration_time,$cookie_path,$cookie_domain,$cookie_secure,$cookie_httponly);
    }
}
?>