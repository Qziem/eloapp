<?php

namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Service\AuthSvc;

class AuthCtrl
{
    /** @var AuthSvc */
    private $authSvc;

    public function __construct(AuthSvc $authSvc)
    {
        $this->authSvc = $authSvc;
    }

    public function isLogged(Response $response): Response
    {
        $isLogged = $this->authSvc->checkIsLogged();
        return $response->withJson(['isLogged' => $isLogged]);
    }

    public function login(Request $request, Response $response): Response
    {
        $json = $request->getBody();
        $payload = json_decode($json, true);
        $logged = $this->authSvc->doLogin($payload['password']);
        return $response->withJson(['logged' => $logged]);
    }
}