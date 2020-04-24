<?php
session_start();
if(!isset($_SESSION['loggedIn'])){
    echo file_get_contents("../HTML/login.html");
}else{
    echo file_get_contents("../HTML/index.html");
}
?>