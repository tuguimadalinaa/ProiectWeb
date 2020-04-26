<?php
class Controller{
    public static function getModel(){
        $model = new DataBase();
        return $model;
    }
    public static function CreateView($viewName){
       require_once("../views/HTML/$viewName.html");
    }
    public static function getSessionHandler(){
        $session = new SessionHandlerModel();
        return $session;
    }

}
?>