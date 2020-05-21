<?php
    //JWT
    define('JWT_KEY',"AFBDS8823GHI");
    define('JWT_ISS',"http://localhost/ProiectWeb/");
    define('JWT_AUD',"http://localhost/ProiectWeb/");
    define('JWT_IAT', time());
    define('JWT_EXP',time()+7200);//valabil 2 ore
?>