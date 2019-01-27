<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\UserRepository;
use Model\Factory\GameFactory;
use Service\UsersSvc;
use Service\RatingCalculator;
use Slim\Http\Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class UsersCtrl
{
    /** @var UsersSvc */
    private $usersSvc;

    public function __construct(UsersSvc $usersSvc) {
        $this->usersSvc = $usersSvc;
    }

    public function getUsers(Response $response): Response
    {
        $userArrayList = $this->usersSvc->getUsers();
        return $response->withJson($userArrayList);
    }

    public function addUser(Request $request, Response $response): Response
    {
        $json = $request->getBody();
        $userArray = json_decode($json, true);
        $responseArray = $this->usersSvc->addUser($userArray);
        return $response->withJson($responseArray);
    }

    public function updateRatings(
        Request $request,
        Response $response
    ): Response {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserCode = $usersCodes['winnerUserCode'];
        $looserUserCode = $usersCodes['looserUserCode'];

        $responseArray = $this->usersSvc->updateRatings($winnerUserCode, $looserUserCode);
        return $response->withJson($responseArray);
    }
}
