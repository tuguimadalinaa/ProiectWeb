<?php
class CookieHandlerModel{
    public static function Cookie($cookie_name,$cookie_value,$cookie_params_array){
        setcookie($cookie_name,$cookie_value,$cookie_params_array);
    }
}
?>