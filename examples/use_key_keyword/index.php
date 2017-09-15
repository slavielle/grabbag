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
        'food-group:../#key'
    ]
]);

var_export($result);

