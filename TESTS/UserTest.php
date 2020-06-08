<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;


class UserTest extends TestCase
{
        public static $username;
        public static $password;
        public static function generateData() :void
        {
            $bytes=random_bytes(16);
            self::$username=substr(bin2hex($bytes),0,9).'@gmail.com';
            $bytes=random_bytes(16);
            self::$password=substr(bin2hex($bytes),0,9);
        }
        public function testRegisterUser() : void
        {
            self::generateData();
            $userinfo=json_encode([
                'username'=>self::$username,
                'password'=>self::$password
            ]);
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIregisterUser");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type:application/json"
            ));
            curl_setopt($curl_resource, CURLOPT_POSTFIELDS,$userinfo);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true); 
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),200);

        }
        public function testRegisterExistingUser() : void
        {
            $userinfo=json_encode([
                'username'=>"vuvu@gmail.com",
                'password'=>"cevaaaa"
            ]);
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIregisterUser");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type:application/json"
            ));
            curl_setopt($curl_resource, CURLOPT_POSTFIELDS,$userinfo);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true); 
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),400);

        }
        public function testRefreshJWT() : void
        {
            $userinfo=json_encode([
                'username'=>"f66a10bc3@gmail.com",
                'password'=>"ec15697f7"
            ]);
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIrefreshJWT");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type:application/json"
            ));
            curl_setopt($curl_resource, CURLOPT_POSTFIELDS,$userinfo);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true); 
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),200);
        }
        public function testRefreshJWTMissingUsername() : void
        {
            $userinfo=json_encode([
                'password'=>"ec15697f7"
            ]);
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIrefreshJWT");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type:application/json"
            ));
            curl_setopt($curl_resource, CURLOPT_POSTFIELDS,$userinfo);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true); 
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),400);
        }
        public function testRefreshJWTEmptyJwt() : void
        {

            self::generateData();
            $userinfo=json_encode([
                'username'=>self::$username,
                'password'=>self::$password
            ]);
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIrefreshJWT");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
                "Content-Type:application/json"
            ));
            curl_setopt($curl_resource, CURLOPT_POSTFIELDS,$userinfo);
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            //curl_close($curl_resource); 
            $responseDecoded=json_decode($response,true); 
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),404);
        }

}
?>