<?php
namespace Middleware;

use Controller\AuthCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PrivilegesIsLogged {
  public function __invoke(Request $request, Response $response, callable $next) {
    $authCtrl = new AuthCtrl();
    if ($authCtrl->isLogged()) {
      return $next($request, $response);
    }

    throw new \Exception('Not Logged');
    return $response;
  }
}