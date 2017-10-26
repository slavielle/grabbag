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
 * Modifier exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class ModifierException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;
    const ERR_2 = 2;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t apply ?consider modifier in a multi-valued path result.',
        self::ERR_2 => 'Unknown modifier "%s".',
    ];
}