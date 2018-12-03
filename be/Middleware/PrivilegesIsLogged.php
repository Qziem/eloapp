<?php
namespace Middleware;

use Controller\AuthCtrl;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PrivilegesIsLogged {
  function __construct(AuthCtrl $authCtrl) {
    $this->authCtrl = $authCtrl;
  }

  public function __invoke(Request $request, Response $response, callable $next) {
    if ($this->authCtrl->checkIsLogged()) {
      return $next($request, $response);
    }

    throw new \Exception('Not Logged');
    return $response;
  }
}