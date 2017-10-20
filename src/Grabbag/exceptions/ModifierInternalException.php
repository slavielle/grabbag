<?php

namespace Grabbag\exceptions;

/**
 * Modifier internal exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class ModifierInternalException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Unable to apply ?unique modifier on this result scope.',
    ];
}