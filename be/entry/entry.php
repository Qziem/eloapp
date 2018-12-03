<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use entry\EloApp;
use Controller\TestCtrl;
use Controller\UsersCtrl;
use Controller\AuthCtrl;
use Controller\RatingsHistoryCtrl;
use Controller\RemoveGameCtrl;

use Middleware\PrivilegesIsLogged;

require 'autoload.php';
require '../vendor/autoload.php';

$app = new EloApp;

$app->get('/auth/isLogged', ['Controller\AuthCtrl', 'isLogged']);

$app->post('/auth/login', ['Controller\AuthCtrl', 'login']);

$app->get('/users', ['Controller\UsersCtrl', 'getUsers'])
  ->add('Middleware\PrivilegesIsLogged');

$app->post('/users', ['Controller\UsersCtrl', 'addUser'])
  ->add('Middleware\PrivilegesIsLogged');

$app->put('/users/update_ratings', ['Controller\UsersCtrl', 'updateRatings'])
  ->add('Middleware\PrivilegesIsLogged');

$app->get('/ratings_history/{userNid}', ['Controller\RatingsHistoryCtrl', 'getRatingsHistory'])
  ->add('Middleware\PrivilegesIsLogged');

$app->delete('/remove_game/{userNid}', ['Controller\RemoveGameCtrl', 'removeLastGameIfPossible'])
  ->add('Middleware\PrivilegesIsLogged');

$app->run();
