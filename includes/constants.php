<?php 

    define('TOKEN_VERSION', "V2");
    define('TOKEN_ADMIN', "admin");
    define('TOKEN_EMPLOYEE', "employee");
    define('TOKEN_ERROR', "error");

    define('DB_ERROR', 001);
    define('USER_CREATED', 101);
    define('USER_EXISTS', 102);
    define('USER_FAILURE', 103); 
    define('USER_AUTHENTICATED', 201);
    define('USER_NOT_FOUND', 202); 
    define('USER_PASSWORD_DO_NOT_MATCH', 203);
    define('USER_NOT_ACTIVE', 204);
    define('PASSWORD_CHANGED', 301);
    define('PASSWORD_DO_NOT_MATCH', 302);
    define('PASSWORD_NOT_CHANGED', 303);
    define('GET_USERS_SUCCESS', 401);
    define('GET_USERS_FAILURE', 402);
    define('DELETE_USER_SUCCESS', 501);
    define('DELETE_USER_FAILURE', 502);