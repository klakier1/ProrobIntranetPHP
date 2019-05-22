<?php 
//    define('DB_HOST', 'ec2-54-195-252-243.eu-west-1.compute.amazonaws.com');
//    define('DB_USER', 'esqaxbtuslanyh');
//    define('DB_PASSWORD', '8baab7795ad0279db771b013a6d5a38f3b046184655e05792654ff4095cddd91');
//    define('DB_NAME', 'd8ep196q5715v6');
    
    define('DB_ERROR', 001);
    define('USER_CREATED', 101);
    define('USER_EXISTS', 102);
    define('USER_FAILURE', 103); 
    define('USER_AUTHENTICATED', 201);
    define('USER_NOT_FOUND', 202); 
    define('USER_PASSWORD_DO_NOT_MATCH', 203);
    define('PASSWORD_CHANGED', 301);
    define('PASSWORD_DO_NOT_MATCH', 302);
    define('PASSWORD_NOT_CHANGED', 303);
    define('GET_USERS_SUCCESS', 401);
    define('GET_USERS_FAILURE', 402);
    define('DELETE_USER_SUCCESS', 501);
    define('DELETE_USER_FAILURE', 502);