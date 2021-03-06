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
 * Item exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class ItemException extends BaseException
{
    // Exception codes
    const ERR_1 = 1;

    // Exception messages
    const MESSAGE = [
        self::ERR_1 => 'Can\'t pop an empty stack',
    ];

}