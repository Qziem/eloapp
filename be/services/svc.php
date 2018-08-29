<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../config.php';
require '../vendor/autoload.php';
require 'UsersCtrl.php';

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['db'] = function ($container) {
    $db = $container["settings"]["db"];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
};

$app->get('/users', function (Request $request, Response $response, array $args) {
    sleep(1);
    $usersSvc = new UsersCtrl($this->db);
    $respArray = $usersSvc->getUsers();
    return $response->withJson($respArray);
});

$app->post('/users/update_ratings', function (Request $request, Response $response, array $args) {
    sleep(1);

    $json = $request->getBody();
    $usersCodes = json_decode($json, true);
    $winnerUserNid = $usersCodes['winnerUserNid'];
    $looserUserNid = $usersCodes['looserUserNid'];

    $usersSvc = new UsersCtrl($this->db);
    $usersSvc->updateRatings($winnerUserNid, $looserUserNid);
    return $response->withJson([]);
});

$app->run();
