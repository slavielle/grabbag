<?php

namespace Grabbag\exceptions;


class CantApplyConsiderModifierException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t apply ?consider modifier in a multi-valued path result.',
    ];
}