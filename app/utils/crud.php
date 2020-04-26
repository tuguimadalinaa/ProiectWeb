<?php
session_start();
class BD{
    private static $dbContext = null;
    public static function getConnection(){
        if(is_null(self::$dbContext))
        {
            self::$dbContext = new PDO('mysql:host=localhost;dbname=ProiectWeb', 'ProiectWeb', 'ProiectWeb');
            return self::$dbContext;
        }
        else{
            return self::$dbContext;
        }
    }
    
}
function approveRequest($password,$inputPassword){
    if(strcmp($password, $inputPassword)==0){
        return '0';
    }
    if(is_null($password)==false){
        return '2';
    }
    return '1';
}
class CRUD{
    public function read($userName, $password){
        $checkUser = 'SELECT * from users';
        $connection  = BD::getConnection()->prepare($checkUser);
        $connection->execute();
        $result = $connection -> fetchAll();
        return $result;
        /*$checkUser = 'SELECT * from users where username='."'".$userName."'";
        $connection  = BD::getConnection()->prepare($checkUser);
        $connection->execute();
        $result = $connection -> fetchAll();
        foreach( $result as $row ) {
            $response = approveRequest($password,$result[0][2]);
            if($response=='0'){
                $_SESSION["loggedIn"] = "true";
            }
            $jsonResponse = json_encode(array("status"=>$response));
            return $jsonResponse;
        }
        $jsonResponse = json_encode(array("status"=>'1'));
        return $jsonResponse;*/
    }
    public function create($userName,$password){
        $checkUser = 'SELECT * from users where username='."'".$userName."'";
        $connection = BD::getConnection()->prepare($checkUser);
        $connection->execute();
        $result = $connection -> fetchAll();
        if(empty($result)==true){
            $checkUser = 'INSERT INTO users(username,password,logged) values('."'".$userName."'".','."'".$password."'".",'"."no"."')";
            $connection = BD::getConnection()->prepare($checkUser);
            $connection->execute();
            return json_encode(array("status"=>'1'));
        }
        return json_encode(array("status"=>'0'));
    }
}
 /*$username = $_REQUEST["username"];
$password = $_REQUEST["password"];
 $method = $_REQUEST["method"];
$crud = new CRUD();
if($method == "CHECK_USER_IN_DB"){
    $response = $crud->read($username,$password);
}
else if($method=="SIGN_UP"){
    $response = $crud->create($username,$password);
}
echo  $response;*/
?>