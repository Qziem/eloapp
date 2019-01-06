<?php
session_start();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use api\EloApp;
use Controller\UsersCtrl;
use Controller\AuthCtrl;
use Controller\RatingsHistoryCtrl;
use Controller\RemoveGameCtrl;

use Middleware\PrivilegesIsLogged;

require 'autoload.php';

$app = new EloApp;

$app->get('/auth/isLogged', [AuthCtrl::class, 'isLogged']);

$app->post('/auth/login', [AuthCtrl::class, 'login']);

$app->get('/users', [UsersCtrl::class, 'getUsers'])
    ->add(PrivilegesIsLogged::class);

$app->post('/users', [UsersCtrl::class, 'addUser'])
    ->add(PrivilegesIsLogged::class);

$app->put('/users/update_ratings', [UsersCtrl::class, 'updateRatings'])
    ->add(PrivilegesIsLogged::class);

$app->get('/ratings_history/{userNid}', [RatingsHistoryCtrl::class, 'getRatingsHistory'])
    ->add(PrivilegesIsLogged::class);

$app->delete('/remove_game/{code}', [RemoveGameCtrl::class, 'removeLastGameIfPossible'])
    ->add(PrivilegesIsLogged::class);

$app->run();
