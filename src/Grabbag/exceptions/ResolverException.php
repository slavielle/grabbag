<?php

namespace Grabbag\exceptions;

/**
 * Item exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class ResolverException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;
    const ERR_3 = 3;
    const ERR_4 = 4;
    const ERR_5 = 5;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t resolve.',
        self::ERR_2 => 'Trying to apply %%any on non traversable value.',
        self::ERR_3 => 'Parameters passed to method thrown an exception.',
        self::ERR_4 => 'Can\'t resolve "%s" on item.',
        self::ERR_5 => 'Can\'t resolve "%s" on array.',
    ];
}