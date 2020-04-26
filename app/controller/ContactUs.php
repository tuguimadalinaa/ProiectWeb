<?php
class ContactUs extends Controller{
    public static function test(){
        print_r(self::query("SELECT * from users"));
    }
}
?>