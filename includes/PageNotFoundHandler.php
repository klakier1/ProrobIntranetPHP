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
            return standardResponse(new Response(), 404, true, "Page not found");
        };
    }
}
