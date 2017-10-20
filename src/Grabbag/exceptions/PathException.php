<?php

namespace Grabbag\exceptions;

/**
 * Path exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class PathException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;
    const ERR_3 = 3;
    const ERR_4 = 4;
    const ERR_5 = 5;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Numerical value encoutered without "#".',
        self::ERR_2 => 'Can\'t parse parameter "%s".',
        self::ERR_3 => 'Can \'t parse path near "%s".',
        self::ERR_4 => 'Keyword "#%s" is not implemented.',
        self::ERR_5 => 'Unknown keyword "#%s" in path.',
    ];
}