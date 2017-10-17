<?php

namespace Grabbag\exceptions;

class ResolveItemStackEmptyException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t pop an empty stack',
    ];

}