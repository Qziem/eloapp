<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'config.php';
require 'vendor/autoload.php';
require 'Entity/User.php';
require 'Entity/Game.php';
require 'Controller/UsersCtrl.php';
require 'Controller/AuthCtrl.php';
require 'Controller/RatingsHistoryCtrl.php';
require 'Util/Helpers.php';
$container = require 'container.php';

$app = new \Slim\App($container);

$app->get('/auth/isLogged', function (Request $request, Response $response) {
    $isLogged = AuthCtrl::isLogged();
    return $response->withJson(['isLogged' => $isLogged]);
});

$app->post('/auth/login', function (Request $request, Response $response) {
    $authCtrl = new AuthCtrl();
    $json = $request->getBody();
    $payload = json_decode($json, true);
    $logged = $authCtrl->login($payload['password']);
    return $response->withJson(['logged' => $logged]);
});

$app->get('/users', function (Request $request, Response $response) {
    AuthCtrl::assertIsLogged();

    $usersCtrl = new UsersCtrl($this->em);
    $respArray = $usersCtrl->getUsers();
    return $response->withJson($respArray);
});

$app->post('/users', function (Request $request, Response $response) {
    AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $user = json_decode($json, true);

    $usersCtrl = new UsersCtrl($this->em);
    $respArray = $usersCtrl->addUser($user);
    return $response->withJson([]);
});

$app->put('/users/update_ratings', function (Request $request, Response $response) {
    AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $usersCodes = json_decode($json, true);
    $winnerUserNid = $usersCodes['winnerUserNid'];
    $looserUserNid = $usersCodes['looserUserNid'];

    $usersCtrl = new UsersCtrl($this->em);
    $usersCtrl->updateRatings($winnerUserNid, $looserUserNid);
    return $response->withJson([]);
});

$app->get('/ratings_history/{userNid}', function (Request $request, Response $response, array $args) {
    AuthCtrl::assertIsLogged();

    $userNid = (int) $args['userNid'];
    $ratingsHistoryCtrl = new RatingsHistoryCtrl($this->em);
    $respArray = $ratingsHistoryCtrl->getRatingsHistory($userNid);
    return $response->withJson($respArray);
});

$app->run();
