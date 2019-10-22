<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

require '../vendor/autoload.php';
require '../includes/responseProcess.php';
require '../includes/dbOperation.php';
require '../includes/token.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

// Register middleware
require '../src/middleware.php';

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");


    /*$db = new DbConnect();

    if($db->connect() != null){
        $response->getBody()->write(' Connection OK');
    }*/

    return $response;
});

/*
    endpoint: login
    parameters: email, password
    method: POST
*/
$app->post('/login', function (Request $request, Response $response, array $args) {
    if(!haveEmptyParameters(array('email' ,'password'), $request, $response)){ 
        $request_data = $request->getParsedBody(); 
        $db = new DbOperation; 
        $user_id = -1;
        $role = "";
        $result = $db->login(
            $request_data['email'], 
            $request_data['password'], 
            $user_id, 
            $role);
    
        if($result == USER_NOT_FOUND){
            return $response = standardResponse($response, 422, true, 'User not found');
        }else if($result == USER_NOT_ACTIVE){
            return $response = standardResponse($response, 422, true, 'User not active'); 
        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            return $response = standardResponse($response, 401, true, 'Wrong password');  
        }else if($result == USER_AUTHENTICATED){
            $token = getToken($user_id, $role);
            return $response = standardResponse($response, 200, false, 'Token generated', ['token' => $token]);
        }else if($result == DB_ERROR){
            return $response = standardResponse($response, 500, true, 'Database error');
        }
    }else{
        return $response;
    }  
});

$app->group('/api', function(\Slim\App $app) {

    /*
        endpoint: user
        parameters: email, password, name, surname
        method: POST
    */
    $app->post('/user', function(Request $request, Response $response){
        $token = $request->getAttribute("decoded_token_data");
        if (checkTokenData($token) == TOKEN_ADMIN){
            /* Admin authorized */
            if(!haveEmptyParameters(array(
                'email', 
                'pass', 
                'role', 
                'active', 
                'first_name', 
                'last_name', 
                'title', 
                'phone', 
                'days_availabe', 
                'notify'
                ), $request, $response)){

                $request_data = $request->getParsedBody(); 
                $db = new DbOperation; 
                $result = $db->createUser(
                    $request_data['email'], 
                    $request_data['pass'], 
                    $request_data['role'], 
                    $request_data['active'], 
                    $request_data['first_name'], 
                    $request_data['last_name'], 
                    $request_data['title'], 
                    $request_data['phone'], 
                    $request_data['days_availabe'], 
                    $request_data['notify']);
                
                if($result == USER_CREATED){
                    return $response = standardResponse($response, 201, false, 'User created successfully'); 
                }else if($result == USER_FAILURE){
                    return $response = standardResponse($response, 422, true, 'Some error occurred');      
                }else if($result == USER_EXISTS){
                    return $response = standardResponse($response, 422, true, 'User Already Exists');    
                }else if($result == DB_ERROR){
                    return $response = standardResponse($response, 500, true, 'Database error');  
                }
            }else{
                return $response;  
            }
        } else {
            /* No scope so respond with 401 Unauthorized */
            return $response = standardResponse($response, 401, true, 'No admin privileges'); 
        }
    });

    /*
        endpoint: user
        parameters:
        method: GET
    */
    $app->get('/user[/{params:.*}]', function(Request $request, Response $response, $args){
        //get arguments
        $token = $request->getAttribute("decoded_token_data");
        $params = array_filter(explode('/', $args['params']));

        switch($role = checkTokenData($token)){
            case TOKEN_ADMIN:{
                /* Admin authorized  */
              
                /* no args          - all users, all data*************************************************** */
                if(count($params) == 0 ){
                    $db = new DbOperation;
                    $result = $db->getAllUsers($ret);
                    
                    if($result == GET_USERS_SUCCESS){
                        return $response = standardResponse($response, 200, false, 'Get users successfull', $ret); 
                    }else if($result == GET_USERS_FAILURE){
                        return $response = standardResponse($response, 422, true, 'Some error occurred');         
                    }else if($result == DB_ERROR){
                        return $response = standardResponse($response, 500, true, 'Database error');  
                    }
                }
                /****************************************************************************************** */ 
            }
            case TOKEN_EMPLOYEE:{

                /* /user/id/[0-9]   -one user, short data************************************************** */
                if(count($params) == 2 && $params[0] == 'id'){
                    $request_id = intval($params[1]);
                    if($token['id'] == $request_id || $role == TOKEN_ADMIN) //admin can get any user
                    {
                        $db = new DbOperation;
                        $result = $db->getUserShort($request_id, $ret);
                        
                        if($result == GET_USERS_SUCCESS){
                            return $response = standardResponse($response, 200, false, 'Get user successfull', $ret); 
                        }else if($result == GET_USERS_FAILURE){
                            return $response = standardResponse($response, 422, true, 'Some error occurred');    
                        }else if($result == GET_USERS_NOT_FOUND){
                            return $response = standardResponse($response, 422, true, 'User not found');       
                        }else if($result == DB_ERROR){
                            return $response = standardResponse($response, 500, true, 'Database error');  
                        }
                    }
                }
                return $response = standardResponse($response, 400, true, 'Bad Request');
                /**************************************************************************************** */

                break;
            }
            case TOKEN_ERROR:{
                return $response = standardResponse($response, 400, true, 'Token invalid'); 
            }
        }
    });

    /*
        endpoint: user
        parameters: user/email/... ;  user/user_id/...
        method: DELETE
    */
    $app->delete('/user[/{params:.*}]', function(Request $request, Response $response, $args){
        $token = $request->getAttribute("decoded_token_data");
        if (checkTokenData($token) == TOKEN_ADMIN) {
            /* Admin authorized  */
            $params = array_filter(explode('/', $args['params']));

            if(count($params) != 2)
                return $response = standardResponse($response, 400, true, 'Bad Request');

            switch($params[0]){
                case "email": {
                        $email = $params[1];
                        if(isValidEmail($email)){
                            $db = new DbOperation;
                            $result = $db->deleteUsersByEmail($email);
                        }else{
                            return $response = standardResponse($response, 422, true, 'Invalid email');
                        }
                    break;
                }
                case "id": {
                        $id = intval($params[1]);
                        if($id > 0){
                            $db = new DbOperation;
                            $result = $db->deleteUsersById($id);
                        }else{
                            return $response = standardResponse($response, 422, true, 'Wrong ID');
                        }
                    break;
                }
                default: {
                    return $response = standardResponse($response, 400, true, 'Bad Request');
                }
            }

            if($result == DELETE_USER_SUCCESS){
                return $response = standardResponse($response, 200, false, 'User has been deleted', $ret); 
            }else if($result == DELETE_USER_FAILURE){
                return $response = standardResponse($response, 422, true, 'Some error occurred');    
            }else if($result == USER_NOT_FOUND){
                return $response = standardResponse($response, 422, true, 'User not found');      
            }else if($result == DB_ERROR){
                return $response = standardResponse($response, 500, true, 'Database error');  
            }
            return $response;
        }else{
            /* No scope so respond with 401 Unauthorized */
            return $response = standardResponse($response, 401, true, 'No admin privileges'); 
        }
    });

    /*
        endpoint: timesheet
        parameters: timesheet/user_id/...
        method: GET
    */
    $app->get('/timesheet[/{params:.*}]', function(Request $request, Response $response, $args){
        //get arguments
        $token = $request->getAttribute("decoded_token_data");
        $params = array_filter(explode('/', $args['params']));

        switch($role = checkTokenData($token)){
            case TOKEN_ADMIN:{
                /* Admin authorized  */
              
                /* no args        - all users, all timesheets*************************************************** */
                if(count($params) == 0 ){
                    $db = new DbOperation;
                    //TODO
                    //$result = $db->getAllUsers($ret);
                    $result = GET_TIMESHEET_FAILURE;

                    if($result == GET_TIMESHEET_SUCCESS){
                        return $response = standardResponse($response, 200, false, 'Get timesheet successfull', $ret); 
                    }else if($result == GET_TIMESHEET_FAILURE){
                        return $response = standardResponse($response, 422, true, 'Some error occurred, TODO');         
                    }else if($result == DB_ERROR){
                        return $response = standardResponse($response, 500, true, 'Database error');  
                    }
                }
                /****************************************************************************************** */ 
            }
            case TOKEN_EMPLOYEE:{

                /* /timesheet/id/[0-9]   -one user, all data************************************************** */
                if(count($params) == 2 && $params[0] == 'id'){
                    $request_id = intval($params[1]);
                    if($token['id'] == $request_id || $role == TOKEN_ADMIN) //admin can get any user
                    {
                        $db = new DbOperation;
                        $result = $db->getTimesheet($request_id, $ret);
                        
                        if($result == GET_TIMESHEET_SUCCESS){
                            return $response = standardResponse($response, 200, false, 'Get timesheet successfull', $ret); 
                        }else if($result == GET_TIMESHEET_FAILURE){
                            return $response = standardResponse($response, 422, true, 'Some error occurred');     
                        }else if($result == DB_ERROR){
                            return $response = standardResponse($response, 500, true, 'Database error');  
                        }
                    }
                }
                return $response = standardResponse($response, 400, true, 'Bad Request');
                /**************************************************************************************** */

                break;
            }
            case TOKEN_ERROR:{
                return $response = standardResponse($response, 400, true, 'Token invalid'); 
            }
        }
    });

});

function haveEmptyParameters($required_params, Request $request, Response &$response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $text = 'Required parameters: ' . substr($error_params, 0, -2) . ' are missing';
        $response = standardResponse($response, 422, true, $text);
    }

    return $error;
}

function isValidEmail(&$email){
    // Remove all illegal characters from email
    $email_filtred = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Compare with orginal
    if(strcmp($email, $email_filtred) != 0)
        return false;

    // Validate e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return false;
    
    // In this point must be valid
    return true;    
}

$app->run();
