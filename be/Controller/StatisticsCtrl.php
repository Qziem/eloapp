<?php declare(strict_types = 1);

namespace Controller;

use Service\StatisticsChartDataSvc;
use Slim\Http\Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class StatisticsCtrl
{
    /** @var StatisticsChartDataSvc */
    private $chartDataSvc;

    public function __construct(
        StatisticsChartDataSvc $chartDataSvc
    ) {
        $this->chartDataSvc = $chartDataSvc;
    }

    public function getChartData(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $users = isset($queryParams['users']) && is_string($queryParams['users'])
            ? explode(',', $queryParams['users'])
            : [];

        $output = $this->chartDataSvc->loadData($users);

        return $response->withJson(['status' => 'success', 'data' => $output]);
    }
}