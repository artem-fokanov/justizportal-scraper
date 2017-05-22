<?php
require_once 'autoload.php';

// default registerant;
$registerant = 'HRB';

$options = getopt('r:', ['registered:']);

if (!empty($options)) {
    foreach ($options as $opt => $value) {

        if (in_array($opt, ['r', 'registered']) && in_array(strtoupper($value), ['HRA', 'HRB'])) {
            $registerant = $value;
        }

    }
}

$controller = new \Controller();
$controller->parser($registerant);