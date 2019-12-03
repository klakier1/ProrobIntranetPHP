<?php

namespace Klakier\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MyMiddleware
{
    private $allowedPath = "/";

    public function __construct(array $options = [])
    {
        if (isset($options["path"])) {
            $this->allowedPath = $options["path"];
        }
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {

        echo $request->getUri()->getPath()[0];
        echo "\n";
        echo $request->getUri()->getPath();
        echo "\n";
        
        if ($request->getUri()->getPath()[0] != "/")
            $path = "/" . $request->getUri()->getPath();
        echo $path;
        if (strpos($path, $this->allowedPath) === 0) {

            $token = $request->getAttribute("decoded_token_data");
            $role = checkTokenData($token);
            if ($role == TOKEN_ADMIN || $role == TOKEN_EMPLOYEE) {
                $request = $request->withAttribute("role", $role);  //set Attribute
                $response = $next($request, $response);             //call next middleware
                return $response;
            } else {
                $response = standardResponse($response, 401, true, "Token verification failed");
                return $response;
            }
        } else {
            $response = $next($request, $response);
            return $response;
        }
    }
}
