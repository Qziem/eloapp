<?php

namespace Middleware\Validation;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

abstract class ValidationAbstract {
    abstract protected function validate(Request $request): ?string;
    
    public function __invoke(
            Request $request,
            Response $response,
            callable $next
    ) {
        $warningMsg = $this->validate($request);
        if ($warningMsg) {
            return $response->withJson(['status' => 'warning', 'warningMsg' => $warningMsg]);
        }

        return $next($request, $response);
    }
}
