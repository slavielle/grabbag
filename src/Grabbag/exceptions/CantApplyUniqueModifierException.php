<?php

namespace Grabbag\exceptions;


class CantApplyUniqueModifierException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Unable to apply ?unique modifier on this result scope.',
    ];
}