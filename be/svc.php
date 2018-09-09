<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

require 'config.php';
require 'vendor/autoload.php';
require 'Entity/User.php';
require 'Controller/UsersCtrl.php';
require 'Controller/AuthCtrl.php';
require 'Controller/RatingsHistoryCtrl.php';
require 'Util/Helpers.php';

$container = new Slim\Container(['settings' => $config]);

$container['db'] = function ($container) {
    $db = $container["settings"]["db"];
    $pdo = new PDO(
        'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'],
        $db['pass'],
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
};

$container['em'] = function ($container) {
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $container['settings']['doctrine']['metadata_dirs'],
        $container['settings']['doctrine']['dev_mode']
    );

    $config->setMetadataDriverImpl(
        new AnnotationDriver(
            new AnnotationReader,
            $container['settings']['doctrine']['metadata_dirs']
        )
    );

    $config->setMetadataCacheImpl(
        new FilesystemCache(
            $container['settings']['doctrine']['cache_dir']
        )
    );

    return \Doctrine\ORM\EntityManager::create($container['settings']['doctrine']['connection'], $config);
};

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

    $usersCtrl = new UsersCtrl($this->db, $this->em);
    $respArray = $usersCtrl->getUsers();
    return $response->withJson($respArray);
});

$app->post('/users', function (Request $request, Response $response) {
    AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $user = json_decode($json, true);

    $usersCtrl = new UsersCtrl($this->db, $this->em);
    $respArray = $usersCtrl->addUser($user);
    return $response->withJson([]);
});

$app->put('/users/update_ratings', function (Request $request, Response $response) {
    AuthCtrl::assertIsLogged();

    $json = $request->getBody();
    $usersCodes = json_decode($json, true);
    $winnerUserNid = $usersCodes['winnerUserNid'];
    $looserUserNid = $usersCodes['looserUserNid'];

    $usersCtrl = new UsersCtrl($this->db, $this->em);
    $usersCtrl->updateRatings($winnerUserNid, $looserUserNid);
    return $response->withJson([]);
});

$app->get('/ratings_history/{userNid}', function (Request $request, Response $response, array $args) {
    AuthCtrl::assertIsLogged();

    $userNid = $args['userNid'];
    $ratingsHistoryCtrl = new RatingsHistoryCtrl($this->db);
    $respArray = $ratingsHistoryCtrl->getRatingsHistory($userNid);
    return $response->withJson($respArray);
});

$app->run();
