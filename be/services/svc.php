<?php

use Eloapp\services as Services;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

session_start();

require __DIR__.'/../config.php';
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/UsersCtrl.php';
require __DIR__.'/AuthCtrl.php';
require __DIR__.'/RatingsHistoryCtrl.php';

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['db'] = function ($container) {
    $db = $container['settings']['db'];
    $pdo = new PDO(
        'mysql:host='.$db['host'].';dbname='.$db['dbname'],
        $db['user'],
        $db['pass'],
        array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    return $pdo;
};

// var_dump($_SERVER); exit;

$app->get('/auth/isLogged', function (Request $request, Response $response, array $args) {
    $isLogged = Services\AuthCtrl::isLogged();

    return $response->withJson(['isLogged' => $isLogged]);
});

$app->post('/auth/login', function (Request $request, Response $response, array $args) {
    $authCtrl = new Services\AuthCtrl();
    $json = $request->getBody();
    $payload = json_decode($json, true);
    $logged = $authCtrl->login($payload['password']);

    return $response->withJson(['logged' => $logged]);
});

$app->get('/users', function (Request $request, Response $response, array $args) {
    Services\AuthCtrl::assertIsLogged();

    $usersCtrl = new Services\UsersCtrl($this->db);
    $respArray = $usersCtrl->getUsers();

    return $response->withJson($respArray);
});

$app->post('/users', function (Request $request, Response $response, array $args) {
    Services\AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $user = json_decode($json, true);

    $usersCtrl = new Services\UsersCtrl($this->db);
    $respArray = $usersCtrl->addUser($user);

    return $response->withJson([]);
});

$app->put('/users/update_ratings', function (Request $request, Response $response, array $args) {
    Services\AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $usersCodes = json_decode($json, true);
    $winnerUserNid = $usersCodes['winnerUserNid'];
    $looserUserNid = $usersCodes['looserUserNid'];

    $usersCtrl = new Services\UsersCtrl($this->db);
    $usersCtrl->updateRatings($winnerUserNid, $looserUserNid);

    return $response->withJson([]);
});

$app->get('/ratings_history/{userNid}', function (Request $request, Response $response, array $args) {
    Services\AuthCtrl::assertIsLogged();

    $userNid = $args['userNid'];
    $ratingsHistoryCtrl = new Services\RatingsHistoryCtrl($this->db);
    $respArray = $ratingsHistoryCtrl->getRatingsHistory($userNid);

    return $response->withJson($respArray);
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->run();
