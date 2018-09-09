<?php

use Eloapp\services as Services;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__.'/config.php';
require __DIR__.'/vendor/autoload.php';

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

$app->add(SessionMiddleware::fromSymmetricKeyDefaults(
    'mBC51OKVvdie2cjfdSBenu59nfNfhwkedkJVNabosTw=',
    1200// 20 minutes
));

$app->get('/auth/isLogged', function (Request $request, Response $response, array $args) {
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    // $isLogged = Services\AuthCtrl::isLogged($session);
    $isLogged = false;

    return $response->withJson(['isLogged' => $isLogged]);
});

$app->post('/auth/login', function (Request $request, Response $response, array $args) {
    $authCtrl = new Services\AuthCtrl();
    $json = $request->getBody();
    $payload = json_decode($json, true);
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

    $logged = $authCtrl->login($payload['password'], $session);

    return $response->withJson(['logged' => $logged]);
});

//group for routes which requieres to be loggged in to system
$app->group('', function () use ($app) {
    $app->get('/users', function (Request $request, Response $response, array $args) {
        $usersCtrl = new Services\UsersCtrl($this->db);
        $respArray = $usersCtrl->getUsers();

        return $response->withJson($respArray);
    });

    $app->post('/users', function (Request $request, Response $response, array $args) {
        $json = $request->getBody();
        $user = json_decode($json, true);

        $usersCtrl = new Services\UsersCtrl($this->db);
        $respArray = $usersCtrl->addUser($user);

        return $response->withJson([]);
    });

    $app->put('/users/update_ratings', function (Request $request, Response $response, array $args) {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserNid = $usersCodes['winnerUserNid'];
        $looserUserNid = $usersCodes['looserUserNid'];

        $usersCtrl = new Services\UsersCtrl($this->db);
        $usersCtrl->updateRatings($winnerUserNid, $looserUserNid);

        return $response->withJson([]);
    });

    $app->get('/ratings_history/{userNid}', function (Request $request, Response $response, array $args) {
        $userNid = $args['userNid'];
        $ratingsHistoryCtrl = new Services\RatingsHistoryCtrl($this->db);
        $respArray = $ratingsHistoryCtrl->getRatingsHistory($userNid);

        return $response->withJson($respArray);
    });
})->add(function ($request, $response, $next) {
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    if (Services\AuthCtrl::isLogged($session)) {
        $response = $next($request, $response);
    } else {
        $response = $response->withStatus(405);
    }

    return $response;
});

$app->run();
