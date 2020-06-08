<?php

//include 'auth_jwt.php';

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
        $checkUser = 'SELECT * from users where username=?';
        $connection  = DataBase::connect()->prepare($checkUser);
        $connection->execute(array($userName));
        $result = $connection -> fetchAll();
        $response = -10;
        if($result == null){
            $jwt="";
        } else {
            foreach( $result as $row ) {
                $response = self::approveRequest($password,$result[0][1]);
                if($response==0)
                {
                    $jwt=Auth::jwtGenerate($userName,$password);
                    $updateLogged = 'UPDATE users SET logged = ? WHERE username = ? ';
                    $connection = DataBase::connect()->prepare($updateLogged);
                    $connection->execute(array('yes',$userName));
                }
                else{
                    //echo "Access denied!";
                    $jwt="";
                }
            }
        }
        $data=array(
            "jwt"=>$jwt,
            "status"=>$response
        );
        $jsonResponse = json_encode($data);
        
        //echo $data['jwt'];
        //echo $data['status'];
        //echo "<pre";
        //print_r($data);
        //echo "</pre>";
        //echo $jsonResponse;
            return $jsonResponse;
        //$jsonResponse = json_encode(array("status"=>'1'));
        //return $jsonResponse;
    }
    public static function addUser($userName, $password){
        $checkUser = 'SELECT * from users where username=?';
        $connection  = DataBase::connect()->prepare($checkUser);
        $connection->execute(array($userName));
        $result = $connection -> fetchAll();
        if(empty($result)==true){
            $checkUser = "INSERT INTO users VALUES(?,?,?,?,?,?,?,?)";
            $connection = DataBase::connect()->prepare($checkUser);
            $current_date = date('Y-m-d');
            if($connection->execute(array($userName,$password,'no','0','0','0',$current_date,$current_date)) == true){
                return json_encode(array("status"=>'1')); //adaugat cu succes
            } else {
                return json_encode(array("status"=>'2')); //eroare la adaugare in baza de date
            }
        }
        return json_encode(array("status"=>'3')); //user-ul exista deja in baza de date
    }
    public static function addAccessToken($access_token,$username,$drive){
        if($drive == 'Dropbox'){
            $updateCommand = 'UPDATE users SET Dropbox_access_token = ? WHERE username = ?';
        }
        if($drive == 'OneDrive'){
            $updateCommand = 'UPDATE users SET OneDrive_access_token = ? WHERE username = ?';
        }
        if($drive == 'GoogleDrive'){
            $updateCommand = 'UPDATE users SET GoogleDrive_access_token = ? WHERE username = ?';
        }
        $connection = DataBase::connect()->prepare($updateCommand);
        if($connection->execute(array($access_token,$username)) == true){
            
        } else{
            
        }
    }

    public static function importDataCSV($username,$mode){
        if($username == 'admin@app.com'){
            if($mode == 'Overwrite'){
                $database_data_delete = "DELETE FROM users";
                $database_insert_row = "INSERT INTO users VALUES(?,?,?,?,?,?,?,?)";
                $connection = DataBase::connect()->prepare($database_data_delete);
                $connection->execute();
                $csv_file = fopen('database_imported.csv',"r");
                $rows = 0;
                while(($data_read = fgetcsv($csv_file,1000000,",")) != false){
                   $values = array();
                   $size = count($data_read);
                   $rows++;
                   for($field = 0; $field < $size; $field++){
                       array_push($values,$data_read[$field]);
                   }
                   $connection1 = Database::connect()->prepare($database_insert_row);
                   $connection1->execute($values);
                }
            } else if($mode == 'Append'){
                $database_insert_row = "INSERT INTO users VALUES(?,?,?,?,?,?,?,?)";
                $csv_file = fopen('database_imported.csv',"r");
                $rows = 0;
                while(($data_read = fgetcsv($csv_file,1000000,",")) != false){
                   $values = array();
                   $size = count($data_read);
                   $rows++;
                   for($field = 0; $field < $size; $field++){
                       array_push($values,$data_read[$field]);
                   }
                   $connection1 = Database::connect()->prepare($database_insert_row);
                   $connection1->execute($values);
                }
            } 
            return 'Data imported';
        } else {
            return 'Forbidden Access';
        }
    }

    public static function exportDataCSV($username){
        if($username == 'admin@app.com'){
            $csv_file = fopen('database_exported.csv',"a");
            $database_values = 'SELECT * FROM users';
            $connection = DataBase::connect()->prepare($database_values);
            $connection->execute();
            $result = $connection->fetchAll();
            foreach($result as $registration){
                fputcsv($csv_file,array($registration[0],$registration[1],$registration[2],$registration[3],$registration[4],$registration[5],$registration[6],$registration[7]));
            }
            fclose($csv_file);
            return 'Data exported';
        } else {
            return 'Forbidden Access';
        }
    }

    public static function adminGetLoggedUsers($username){
        if($username == 'admin@app.com'){
            $updateLogged = 'SELECT username FROM users WHERE logged = ? ';
            $connection = DataBase::connect()->prepare($updateLogged);
            $connection->execute(array('yes'));
            $result = $connection->fetchAll();
            $loggedUsers = array();
            foreach($result as $registration){
                if($registration[0] != 'admin@app.com'){
                    array_push($loggedUsers,$registration[0]);
                }
            }
            return array_values($loggedUsers);
        } else {
            return 'Forbidden Access';
        }
    }

    public static function adminEditUser($username,$fields){
        if($username == 'admin@app.com'){
            $checkUser = 'SELECT * from users where username=?';
            $connection  = DataBase::connect()->prepare($checkUser);
            $connection->execute(array($fields['username']));
            $result = $connection -> fetchAll();
            if(empty($result) == false){
                $editUser = 'UPDATE users SET ';
                $newValues = array();
                $ok = 0;
                foreach($fields as $key => $value){
                    if($key != 'username' && $value != "null"){
                        if($ok == 0){
                            if($key == 'new_username'){
                                $editUser = $editUser . 'username' . ' = ? ';
                                array_push($newValues,$value);
                            } else {
                                $editUser = $editUser . $key . ' = ? ';
                                array_push($newValues,$value);
                            }
                            $ok = 1;
                        } else {
                            if($key == 'new_username'){
                                $editUser = $editUser . "," .'username' . ' = ? ';
                                array_push($newValues,$value);
                            } else {
                                $editUser = $editUser . "," . $key . ' = ? ';
                                array_push($newValues,$value);
                            }
                        }
                    }
                }
                array_push($newValues,$fields['username']);
                $editUser = $editUser . "WHERE username = ?";
                $connection = DataBase::connect()->prepare($editUser);
                if($connection->execute($newValues) == true){
                    return 'User fields edited';
                } else {
                    return 'Internal error';
                }
            } else {
                return "User doesn't exist";
            }
        } else {
            return 'Forbidden Access';
        }
    }

    public static function adminAddUser($username,$fields){
        if($username == 'admin@app.com'){
            $checkUser = 'SELECT * from users where username=?';
            $connection  = DataBase::connect()->prepare($checkUser);
            $connection->execute(array($fields['username']));
            $result = $connection -> fetchAll();
            if(empty($result)==true){
                $checkUser = "INSERT INTO users VALUES(?,?,?,?,?,?,?,?)";
                $connection = DataBase::connect()->prepare($checkUser);
                $current_date = date('Y-m-d');
                if($connection->execute(array($fields['username'],$fields['password'],'no','0','0','0',$current_date,$current_date)) == true){
                    return 'User added'; 
                } else {
                    return 'Internal error'; 
                }
            } else {
                return 'Username already exist';
            }
        } else {
            return 'Forbidden Access';
        }
    }

    public static function adminDeleteUser($username,$username_to_delete){
        if($username == 'admin@app.com' && $username_to_delete != 'admin@app.com'){
            $checkUser = 'SELECT * from users where username=?';
            $connection  = DataBase::connect()->prepare($checkUser);
            $connection->execute(array($username_to_delete));
            $result = $connection -> fetchAll();
            if($result != null){
                $deleteUser = 'DELETE FROM users WHERE username = ?';
                $connection = DataBase::connect()->prepare($deleteUser);
                if($connection->execute(array($username_to_delete)) == true){
                    return 'User deleted';
                } else {
                    return 'Internal Error';
                }
            } else {
                return "User doesn't exist";
            }
        } else {
            return 'Forbidden Access';
        }
    }

    public static function adminGetNumberOfUsersLogged($username){
        if($username == 'admin@app.com'){
            $updateLogged = 'SELECT count(*) FROM users WHERE logged = ? ';
            $connection = DataBase::connect()->prepare($updateLogged);
            $connection->execute(array('yes'));
            $result = $connection->fetchAll();
            return $result[0][0];
        } else {
            return 'Forbidden Access';
        }
    }

    public static function userLogOut($username){
        $updateLogged = 'UPDATE users SET logged = ? WHERE username = ? ';
        $connection = DataBase::connect()->prepare($updateLogged);
        $connection->execute(array('no',$username));
        $updateLastLoggedIn = 'UPDATE users SET last_logged_in  = ? WHERE username = ? ';
        $connection = DataBase::connect()->prepare($updateLastLoggedIn);
        $connection->execute(array(date('Y-m-d'),$username));
    }

    public static function getAccessToken($userName,$drive){
        $checkUser = 'SELECT * from users where username=?';
        $connection  = DataBase::connect()->prepare($checkUser);
        $connection->execute(array($userName));
        $result = $connection -> fetchAll();
        if($drive == "OneDrive"){
            if($result[0][3] != '0'){                       
                return json_encode(array("status"=>"200","access_token"=>$result[0][3]));
            }else{
                return json_encode(array("status"=>"401","access_token" =>null));
            }
            
        } else if($drive == "Dropbox"){
            if($result[0][4] != '0'){                       
                return json_encode(array("status"=>"200","access_token"=>$result[0][4]));
            }else{
                return json_encode(array("status"=>"401","access_token" =>null));
            }
        } else {
            if($result[0][5] != '0'){                       
                return json_encode(array("status"=>"200","access_token"=>$result[0][5]));
            }else{
                return json_encode(array("status"=>"401","access_token" =>null));
            }
        }
    
    }
}
?>