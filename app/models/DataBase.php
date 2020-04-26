<?php
class DataBase{
    public static $data='0';
    public static function connect(){
        $dbContext = new PDO('mysql:host=localhost;dbname=ProiectWeb', 'ProiectWeb', 'ProiectWeb');
        return $dbContext;
    }
    public static function query($query, $params=array()){
        $statement = self::connect()->prepare($query);
        $statement->execute($params);
        if(explode(' ',$query)[0]=='SELECT'){
            $data = $statement->fetchAll();
            return $data;
        }
        
    }
    private function approveRequest($password,$inputPassword){
        if(strcmp($password, $inputPassword)==0){
            return '0';
        }
        if(is_null($password)==false){
            return '2';
        }
        return '1';
    }
    public static function getApprovalForLogin($userName, $password){
        $checkUser = 'SELECT * from users where username='."'".$userName."'";
        $connection  = DataBase::connect()->prepare($checkUser);
        $connection->execute();
        $result = $connection -> fetchAll();
        foreach( $result as $row ) {
            $response = self::approveRequest($password,$result[0][2]);
            $jsonResponse = json_encode(array("status"=>$response));
            return $jsonResponse;
        }
        $jsonResponse = json_encode(array("status"=>'1'));
        return $jsonResponse;
    }
    public static function addUser($userName, $password){
        $checkUser = 'SELECT * from users where username='."'".$userName."'";
        $connection  = DataBase::connect()->prepare($checkUser);
        $connection->execute();
        $result = $connection -> fetchAll();
        if(empty($result)==true){
            $checkUser = 'INSERT INTO users(username,password,logged) values('."'".$userName."'".','."'".$password."'".",'"."no"."')";
            $connection = DataBase::connect()->prepare($checkUser);
            $connection->execute();
            return json_encode(array("status"=>'1'));
        }
        return json_encode(array("status"=>'0'));
    }
}
?>