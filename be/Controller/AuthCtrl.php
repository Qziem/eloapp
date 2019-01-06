<?php

namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthCtrl
{
    public function checkIsLogged(): bool
    {
        return isset($_SESSION['isLogged']);
    }

    public function isLogged(Response $response): Response
    {
        $isLogged = $this->checkIsLogged();
        return $response->withJson(['isLogged' => $isLogged]);
    }

    private function doLogin(string $password): bool
    {
        $accessPassword = '31137594f95f0bc00c08a98caf14ed3b5905bbee';
        $isPass = sha1($password) === $accessPassword;
        if ($isPass) {
            $_SESSION['isLogged'] = true;
        }

        return $isPass;
    }

    public function login(Request $request, Response $response): Response
    {
        $json = $request->getBody();
        $payload = json_decode($json, true);
        $logged = $this->doLogin($payload['password']);
        return $response->withJson(['logged' => $logged]);
    }
}