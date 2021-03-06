<?php

$basePath = realpath(dirname(__FILE__));

require_once($basePath . '/../bootstrap.php');

use Grabbag\Grabbag;

echo "json input: \n";
echo json_encode(json_decode(file_get_contents($basePath . '/data.json')), JSON_PRETTY_PRINT);
echo "\n\n";
echo "json output: \n";


$filterFriends = ['Mary', 'Tom', 'Lara'];
$json = json_encode(
    Grabbag::grab(
        json_decode(file_get_contents($basePath . '/data.json')),

        // Here comes the Grabbag query
        [
            // Get my friends selection.
            'my-friends:%any' => [
                '~name:name',
                '?consider' => function ($value, $id) use ($filterFriends) {
                    if ($id === '~name') {
                        return in_array($value, $filterFriends);
                    }
                },
            ],

            // Get my friends selection prefered fruits.
            'fruits-they-like:%any/food/liked/fruits/%any' => [

                '~fruit:.',

                '?consider' => function ($value, $id, $item) use ($filterFriends) {
                    if ($id === '~fruit') {
                        return in_array($item->grab('../../../../name'), ['Mary', 'Tom']);
                    }
                },

                // If some of them love same fruits, no need to list a fruit twice !
                '?unique'


            ]

        ]
    ),
    JSON_PRETTY_PRINT
);

echo $json;