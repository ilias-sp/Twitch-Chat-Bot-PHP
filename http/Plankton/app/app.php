<?php 

include(__DIR__.'/autoload.php');

return array(
    'controllers' => array('default'),
    'model' => new \Model(),

    'request' => array(
        'parameters' => $_REQUEST,
        'method' => $_SERVER['REQUEST_METHOD'],
    )
);
