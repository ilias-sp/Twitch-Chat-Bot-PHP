it calls first the layout.php

app.php -> u define the available controllers/ models/ files to load:

return array(
    'controllers' => array('default', 'films'),
    'model' => new \Model('cinema', 'root', 'root'),

    'request' => array(
        'parameters' => $_REQUEST,
        'method' => $_SERVER['REQUEST_METHOD'],
    )
);

layout renders the header.php first
