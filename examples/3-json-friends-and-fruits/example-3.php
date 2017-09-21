<?php

$basePath = realpath(dirname(__FILE__));

require_once($basePath . '/../bootstrap.php');

use Grabbag\Grabbag;

echo "json input: \n";
echo json_encode(json_decode(file_get_contents($basePath . '/data.json')), JSON_PRETTY_PRINT);
echo "\n\n";
echo "json output: \n";

$data = json_decode(file_get_contents($basePath . '/data.json'));

$hatedFruit = Grabbag::grab($data, ['#any/food/hated/fruits/#any', '?unique']);

$json = json_encode(
    Grabbag::grab(
        $data,

        // Here comes the Grabbag query
        [
            // Get all my friends list.
            'my-friends:#any/name',

            // Get all fruits they love.
            'fruits-they-like:#any/food/liked/fruits/#any' => [
                '~fruit:.',
                '?consider' => function($item, $id) use ($hatedFruit){
                    if($id === '~fruit'){
                        return !in_array($item->get(), $hatedFruit);
                    }
                },

                // If some of them love same fruits, no need to list one fruit twice !
                '?unique' => TRUE
            ]
        ]
    ),
    JSON_PRETTY_PRINT
);

echo $json;