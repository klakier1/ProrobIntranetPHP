<?php

namespace Klakier;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class ErrorHandlerProvider
{
    public function __invoke() {
        return function (ServerRequestInterface $request, ResponseInterface $response, $e)  {
            $ex = ['exceptionCode' => $e->getCode(), 'exceptionMsg' => $e->getMessage()];
            return standardResponse(new Response(), 500, true, "Uncaught exception", $ex);
        };
    }
}

?>