<?php
spl_autoload_register(function ($class_name) {

    $file = 'classes'  . DIRECTORY_SEPARATOR. $class_name . '.php';

    if (file_exists($file)) {
        require $file;
    }
});