<?php

namespace api;

use \DI\ContainerBuilder;

class EloApp extends \DI\Bridge\Slim\App
{
    protected function configureContainer(ContainerBuilder $builder)
    {
        $builder->addDefinitions(__DIR__ . '/containerDIConfig.php');
    }
}