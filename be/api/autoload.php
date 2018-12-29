<?php
spl_autoload_register(function (string $classPath) {
    $newClassPath = str_replace('\\', '/', $classPath);
    require('../' . $newClassPath . '.php');
});
