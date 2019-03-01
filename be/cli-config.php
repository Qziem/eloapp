<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Api\EloApp;

require_once 'Api/autoload.php';

$app = new EloApp;

$container = $app->getContainer();
$em = $container->get(EntityManager::class);

ConsoleRunner::run(
    ConsoleRunner::createHelperSet($em)
);
