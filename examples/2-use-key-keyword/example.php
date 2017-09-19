<?php

$basePath = realpath(dirname(__FILE__));
require_once($basePath . '/../../vendor/autoload.php');

use Grabbag\Grabbag;

$input_data = [
    'fruits' => [
        'orange',
        'banana',
        'apple',
    ],
    'vegetables' => [
        'carrot',
        'tomato',
        'potato'
    ]
];

$result = Grabbag::grab($input_data, [
    '#any/#any' => [
        'food:.',
        'food-type:../#key',
        '?transform' => function ($value, $key) {

            // Remove the trailing 's' from value
            if ($key === 'food-group') {
                return substr($value, 0, -1);
            }
        }
    ]
]);

var_export($result);

