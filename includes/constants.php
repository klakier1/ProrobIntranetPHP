<?php 
    define('DEBUG_TAG','KlakierDebug');
    
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
    define('GET_USERS_NOT_FOUND' , 403);
    define('DELETE_USER_SUCCESS', 501);
    define('DELETE_USER_FAILURE', 502);

    define('GET_TIMESHEET_SUCCESS', 601);
    define('GET_TIMESHEET_FAILURE', 602);
    define('INSERT_TIMESHEETROW_SUCCESS', 603);
    define('INSERT_TIMESHEETROW_FAILURE', 604);
    define('DELETE_TIMESHEETROW_SUCCESS', 605);
    define('DELETE_TIMESHEETROW_FAILURE', 606);
    define('UPDATE_TIMESHEETROW_SUCCESS', 607);
    define('UPDATE_TIMESHEETROW_FAILURE', 608);
    define('TIMESHEET_NOT_FOUND', 609);

    define('GET_COUNTRIES_SUCCESS', 701);
    define('GET_COUNTRIES_FAILURE', 702);
