<?php

$basePath = realpath(dirname(__FILE__));

require_once($basePath . '/../bootstrap.php');

use Grabbag\Grabbag;

$input_data = [
    'my' => [
        'object' => [
            (object)[
                'here' => [
                    "My string 1-1",
                    "My string 1-2"
                ],
                'there' => [
                    "My string 1-3",
                    "My string 1-4"
                ]
            ],
            (object)[
                'here' => [
                    "My string 2-1",
                    "My string 2-2"
                ],
                'there' => [
                    "My string 2-3",
                    "My string 2-4"
                ]
            ],

        ]
    ]
];

echo "\n\nwithout nested path-array:\n";
$result = Grabbag::grab($input_data, 'my/object/%any/here/%any');

var_export($result);

echo "\n\nwith nested path-array:\n";
$result = Grabbag::grab($input_data, [
    "my/object/%any" =>
        [
            "here/%any"
        ]
]);

var_export($result);