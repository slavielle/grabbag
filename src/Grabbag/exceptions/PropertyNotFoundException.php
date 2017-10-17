<?php

namespace Grabbag\exceptions;

class PropertyNotFoundException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t resolve "%s" on item.',
        self::ERR_2 => 'Can\'t resolve "%s" on array.',
    ];
}