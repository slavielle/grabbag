<?php

namespace Grabbag\exceptions;

/**
 * Modifier exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class ModifierException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t apply ?consider modifier in a multi-valued path result.',
    ];
}