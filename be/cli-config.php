<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Slim\Container;

require_once 'svc.php';

// return ConsoleRunner::createHelperSet($container['em']);
ConsoleRunner::run(
    ConsoleRunner::createHelperSet($container['em'])
);