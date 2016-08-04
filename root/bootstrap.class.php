<?php

function myAutoLoader($class) {
    $whitelist = array(
        'game', 'player', 'dice', 'histogram', 'gamemanager',
    );
    if(in_array($class, $whitelist)) return;
    $filename = strtolower($class). ".class.php";
    $file = __SRC_PATH . DIRECTORY_SEPARATOR
        . $class . DIRECTORY_SEPARATOR . $filename;
    if(!file_exists($file)) {
        throw new Exception("Unable to load $file in $filename");
    }
    include_once($file);
}

function diceAutoLoader($class) {
    $filename = strtolower($class). ".class.php";
    $file = __SRC_PATH . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . "dice" . DIRECTORY_SEPARATOR . $filename;
    if(!file_exists($file)) {
        throw new Exception("Unable to load $file in $filename");
    }
    include_once($file);
}

try {
    spl_autoload_register('myAutoLoader');
    spl_autoload_register('diceAutoLoader');
}catch (Exception $e) {
    echo $e->getMessage(), "\n";
}