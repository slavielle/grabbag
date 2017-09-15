<?php

$basePath = realpath(dirname(__FILE__));
require_once($basePath . '/../../vendor/autoload.php');

use Grabbag\Grabbag;

echo "json input: \n";
echo json_encode(json_decode(file_get_contents($basePath . '/data.json')), JSON_PRETTY_PRINT);
echo "\n\n";
echo "json output: \n";

$json = json_encode(
    Grabbag::grab(
        json_decode(file_get_contents($basePath . '/data.json')),

        // Here comes the Grabbag query
        [
            // Get all my friends list.
            'my-friends:#any/name',

            // Get all fruits they love.
            'fruits-they-like:#any/food/liked/fruits/#any' => [
                '.',

                // If some of them love same fruits, no need to list one fruit twice !
                '?unique'
            ]
        ]
    ),
    JSON_PRETTY_PRINT
);

echo $json;