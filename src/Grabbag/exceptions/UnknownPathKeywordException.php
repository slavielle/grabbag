<?php

namespace Grabbag\exceptions;

class UnknownPathKeywordException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Unknown keyword "#%s" in path',
    ];

}
