<?php

namespace Klakier;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class PageNotFoundHandler
{
    public function __invoke()
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) {
            $ret['BASE'] = $_SERVER['BASE'];
            $ret['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            
            return standardResponse(new Response(), 404, true, "Page not found", $ret);
        };
    }
}
