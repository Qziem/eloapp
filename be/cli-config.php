<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'api/index.php';

$container = $app->getContainer();
$em = $container->get(EntityManager::class);

ConsoleRunner::run(
    ConsoleRunner::createHelperSet($em)
);
