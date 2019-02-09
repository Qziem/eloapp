<?php

namespace Middleware\Privileges;

use Service\AuthSvc;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PrivilegesIsLogged
{
    /** @var AuthSvc */
    private $authSvc;

    function __construct(AuthSvc $authSvc)
    {
        $this->authSvc = $authSvc;
    }

    public function __invoke(
        Request $request,
        Response $response,
        callable $next
    ) {
        if ($this->authSvc->checkIsLogged()) {
            return $next($request, $response);
        }

        throw new \Exception('Not Logged');
    }
}