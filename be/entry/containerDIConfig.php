<?php
require '../config.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Tools\Setup;

return [
  'settings.addContentLengthHeader' => $config['addContentLengthHeader'],
  'settings.displayErrorDetails' => $config['displayErrorDetails'],
  EntityManager::class => function () use($config) {
    $configDoctrine = Setup::createAnnotationMetadataConfiguration(
      $config['doctrine']['metadata_dirs'],
      $config['doctrine']['dev_mode']
    );

    $configDoctrine->setMetadataDriverImpl(
        new AnnotationDriver(
            new AnnotationReader,
            $config['doctrine']['metadata_dirs']
        )
    );

    $configDoctrine->setMetadataCacheImpl(
        new FilesystemCache(
          $config['doctrine']['cache_dir']
        )
    );

    $configDoctrine->setProxyDir($config['doctrine']['cache_proxy_dir']);

    return EntityManager::create($config['doctrine']['connection'], $configDoctrine);
	},
];
