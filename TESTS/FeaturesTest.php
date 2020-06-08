<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class FeaturesTest extends TestCase
{
    public static $urls;
    public static function getJwtFirst()
    {
        $userinfo=json_encode([
            'username'=>"zuzuzix@gmail.com",
            'password'=>"zuzuzuzu"
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
        $responseDecoded=json_decode($response,true);
        return $responseDecoded['JWT'];
    }

    public function testGetUrlForCode()
    {
        $jwtul=self::getJwtFirst();
        $drives=array("drive1"=>"GoogleDrive","drive2"=>"OneDrive","drive3"=>"Dropbox");
        foreach($drives as $drive)
        {
            $apiArgs=json_encode(array("drive"=>"${drive}"));
            $curl_resource = curl_init();
            curl_setopt($curl_resource,CURLOPT_URL, "http://localhost/ProiectWeb/app/APIgetCode");
            curl_setopt($curl_resource,CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($curl_resource,CURLOPT_HTTPHEADER,array(
            "Auth: ${jwtul}",
            "Api-Args: ${apiArgs}"
            ));
            curl_setopt($curl_resource,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl_resource,CURLOPT_SSL_VERIFYPEER,false);
            $response=curl_exec($curl_resource);
            self::$urls=$response;
            $this->assertEquals(curl_getinfo($curl_resource,CURLINFO_HTTP_CODE),200);
        }
    }
}