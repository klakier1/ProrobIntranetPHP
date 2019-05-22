<?php

use Psr\Http\Message\ResponseInterface as Response;

function standardResponse(Response $response, Int $httpCode, Bool $error, String $message, Array $extra = null){
    $responseBody = array(); 
    $responseBody['error'] = $error; 
    $responseBody['message'] = $message;
    if($extra != null){
        $responseBody = array_merge($responseBody, $extra);
    }
    $response->write(json_encode($responseBody));
    return $response
        ->withStatus($httpCode)
        ->withHeader('Content-type', 'application/json');
}

?>