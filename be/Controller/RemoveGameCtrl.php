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
        $this->removeGameSvc->removeLastGame($code);
        return $response->withJson(['status' => 'success']);
    }
}
