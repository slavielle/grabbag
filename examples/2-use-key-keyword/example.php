<?php

$basePath = realpath(dirname(__FILE__));

require_once($basePath . '/../bootstrap.php');

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
    '%any/%any' => [
        'food:.',
        'food-type:../%key',
        '?transform' => function ($value, $key) {
            var_export($value);
            // Remove the trailing 's' from value
            if ($key === 'food-type') {
                return substr($value, 0, -1);
            }
            return $value;
        }
    ]
]);

var_export($result);

