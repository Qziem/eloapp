<?php

namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

use Service\RatingsHistorySvc;

class RatingsHistoryCtrl
{
    /** @var RatingsHistorySvc */
    private $ratingsHistorySvc;

    public function __construct(RatingsHistorySvc $ratingsHistorySvc)
    {
        $this->ratingsHistorySvc = $ratingsHistorySvc;
    }

    public function getRatingsHistory(
        Response $response,
        $code
    ): Response {
        $responseArray = $this->ratingsHistorySvc->getRatingsHistory($code);
        return $response->withJson($responseArray);
    }
}
