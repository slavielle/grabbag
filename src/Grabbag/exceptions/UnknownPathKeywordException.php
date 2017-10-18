<?php

namespace Grabbag\exceptions;

class UnknownPathKeywordException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Keyword "#%s" not implemented',
        self::ERR_2 => 'Unknown keyword "#%s" in path',
    ];


}
