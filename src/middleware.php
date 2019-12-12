<?php

use Slim\App;

$app->add(new \Klakier\Middleware\MyMiddleware([
    "path" => "/api"
]));

$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "secure" => false,
    "path" => "/api", /* or ["/api", "/admin"] */
    "attribute" => "decoded_token_data",
    "secret" => getenv("JWT_SECRET"),
    "algorithm" => ["HS256"],
    "error" => function ($response, $arguments) {
        return $response = standardResponse($response, 401, true, $arguments["message"]);
    }
]));
