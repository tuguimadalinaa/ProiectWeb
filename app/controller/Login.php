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
    public static function Cookie($cookie_name,$cookie_value,$cookie_params_array){
        return self::getCookieHandler()->Cookie($cookie_name,$cookie_value,$cookie_params_array);
    }
    public static function validateJwtCookie(){
        if(isset($_COOKIE['loggedIn'])){
            $decoded_jwt = self::getAuth()->jwtDecode($_COOKIE["loggedIn"]);
            if($decoded_jwt == null){
                return 'JWT invalid';
            } else {
                return 'JWT valid';
            }
        } else {
            return 'Cookie not found';
        }
    }
    public static function validateJwtRequest($headers){
        $jwt = null;
        foreach ($headers as $header => $value) {
            if($header == 'Auth'){
                $jwt = $value;
                break;
            }
        }
        if($jwt != null){
            $decoded_jwt = self::getAuth()->jwtDecode($value);
            if($decoded_jwt == null){
                return 'JWT invalid';
            } else {
                return 'JWT valid';
            }
        } else {
            return 'JWT is empty';
        }
    }
}
?>