<?php

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