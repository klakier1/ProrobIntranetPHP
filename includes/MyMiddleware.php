<?php

namespace Klakier\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;


final class MyMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        $response->getBody()->write('BEFORE');
        //$response = $next($request, $response);
        $response->getBody()->write('AFTER');

        return $response;
    }

}

?>