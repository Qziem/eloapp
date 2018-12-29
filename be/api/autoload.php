<?php
spl_autoload_register(function (string $classPath) {
    $newClassPath = str_replace('\\', '/', $classPath);
    require __DIR__ . '/../' . $newClassPath . '.php';
});

require __DIR__.'/../vendor/autoload.php';
