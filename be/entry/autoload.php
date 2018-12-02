<?php
spl_autoload_register(function ($classPath) {
    $newClassPath = str_replace('\\', '/', $classPath);
    require('../' . $newClassPath . '.php');
});
