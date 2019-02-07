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
        string $code
    ): Response {
        $ratingsHistoryArray = $this->ratingsHistorySvc->getRatingsHistory($code);
        return $response->withJson(['status' => 'success', 'ratingsHistory' => $ratingsHistoryArray]);
    }
}
