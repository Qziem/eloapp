<?php

namespace Controller;

use Service\RemoveGameSvc;
use Slim\Http\Response;

class RemoveGameCtrl
{
    /** @var RemoveGameSvc */
    private $removeGameSvc;

    public function __construct(
        RemoveGameSvc $removeGameSvc
    ) {
        $this->removeGameSvc = $removeGameSvc;
    }

    public function removeLastGameIfPossible(
        Response $response,
        string $code
    ): Response {
        $warningMsg = $this->removeGameSvc->validateRemoveLastGame($code);
        if ($warningMsg) {
            return $response->withJson(['status' => 'warning', 'warningMsg' => $warningMsg]);
        }
        
        $this->removeGameSvc->removeLastGame($code);
        return $response->withJson(['status' => 'success']);
    }
}
