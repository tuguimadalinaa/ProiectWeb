<?php

include_once '../JWT/jwt_params.php';
include_once '../JWT/php-jwt-master/php-jwt-master/src/BeforeValidException.php';
include_once '../JWT/php-jwt-master/php-jwt-master/src/ExpiredException.php';
include_once '../JWT/php-jwt-master/php-jwt-master/src/SignatureInvalidException.php';
include_once '../JWT/php-jwt-master/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;

class Auth{
    public static function jwtGenerate($username,$password)
    {
        $token =array(
            "iss"=>JWT_ISS,
            "aud"=>JWT_AUD,
            "iat"=>JWT_IAT,
            "exp"=>JWT_EXP,
            "data"=>array(
                "username"=>$username,
                "password"=>$password
            )
            );
        $jwt= JWT::encode($token,JWT_KEY);
        return $jwt;
    }
    public static function jwtDecode($jwt)
    {
        //echo $jwt;
        $userinfo=NULL;
        try{
            $decoded=JWT::decode($jwt,JWT_KEY,array('HS256'));
            $userinfo=$decoded->data;
        } catch(Exception $e){
            $userinfo = null;
        }
        return $userinfo;
    }
}
?>