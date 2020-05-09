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
        else if(is_null($password)==false){
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
            $checkUser = "INSERT INTO users VALUES(?,?,?,?)";
            $connection = DataBase::connect()->prepare($checkUser);
            if($connection->execute(array($userName,$password,'no','0')) == true){
                return json_encode(array("status"=>'1')); //adaugat cu succes
            } else {
                return json_encode(array("status"=>'2')); //eroare la adaugare in baza de date
            }
        }
        return json_encode(array("status"=>'3')); //user-ul exista deja in baza de date
    }
    public static function addAccessToken($access_token,$username){
        $updateCommand = 'UPDATE users SET access_token = ' . "'" . $access_token . "'" . ' WHERE username = ' . "'" . $username . "'";
        $connection = DataBase::connect()->prepare($updateCommand);
        if($connection->execute() == true){
            
        } else{
            
        }
    }
}
?>