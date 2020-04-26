<?php
session_start();
require_once('routes.php');
function __autoload($class_name){
    if(file_exists('./utils/'. $class_name . '.php')){
        require_once './utils/'. $class_name . '.php';
    }
    else if (file_exists('./controller/'. $class_name . '.php')){
        require_once './controller/'. $class_name . '.php';
    } 
    if(file_exists('./models/'. $class_name . '.php')){
        require_once './models/'. $class_name . '.php';
    }
}
?>