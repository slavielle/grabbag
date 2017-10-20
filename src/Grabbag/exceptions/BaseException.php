<?php

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