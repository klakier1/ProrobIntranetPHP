<?php

namespace Klakier;

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

        $path = $request->getUri()->getPath();

        //Some servers return path as "api/something" but other "/api/something"
        if ($request->getUri()->getPath()[0] != "/")
            $path = "/" . $path;

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
