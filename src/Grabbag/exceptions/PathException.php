<?php

/*
 * This file is part of the Grabbag package.
 *
 * (c) Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    const ERR_6 = 6;
    const ERR_7 = 7;
    const ERR_8 = 8;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Numerical value encoutered without "#".',
        self::ERR_2 => 'Can\'t parse parameter "%s".',
        self::ERR_3 => 'Can \'t parse path near "%s".',
        self::ERR_4 => 'Keyword "#%s" is not implemented.',
        self::ERR_5 => 'Unknown keyword "#%s" in path.',
        self::ERR_6 => 'Unknown special char "%s".',
        self::ERR_7 => 'Keyword "%s" cannot have parameter "%s".',
        self::ERR_8 => 'Symbol "%s" cannot have parameter "%s".',
    ];
}