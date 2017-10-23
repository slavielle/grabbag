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
 * Base exception for Grabbag.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class BaseException extends \Exception
{
    public function __construct($code = 0, $params = [])
    {
        $message = 'Unknown exception.';
        $className = get_class($this);
        if (array_key_exists($code, $className::MESSAGE)) {
            $message = vsprintf($className::MESSAGE[$code], $params);
        }
        parent::__construct($message, $code);
    }
}