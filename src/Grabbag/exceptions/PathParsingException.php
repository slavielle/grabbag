<?php

namespace Grabbag\exceptions;

class PathParsingException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;
    const ERR_3 = 3;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Numerical value encoutered without "#"',
        self::ERR_2 => 'can\'t parse parameter "%s"',
        self::ERR_3 => 'Can \'t parse path near "%s"',
    ];
}