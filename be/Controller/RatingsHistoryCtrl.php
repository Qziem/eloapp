<?php

namespace Controller;

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
        $warningMsg = $this->ratingsHistorySvc->validateUserExist($code);
        if ($warningMsg) {
            return $response->withJson(['status' => 'warning', 'warningMsg' => $warningMsg]);
        }

        $ratingsHistoryArray = $this->ratingsHistorySvc->getRatingsHistory($code);
        return $response->withJson(['status' => 'success', 'ratingsHistory' => $ratingsHistoryArray]);
    }
}
