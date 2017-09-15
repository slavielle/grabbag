<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 15/09/17
 * Time: 12:48
 */

namespace Grabbag\exceptions;


use Throwable;

class ResolveItemStackEmptyException extends \Exception
{
    const CODE_1 = 1;

    public function __construct($code = 0, Throwable $previous = null)
    {
        $message = 'Unknown exception.';
        switch ($code){
            case self::CODE_1:
                $message = 'Can\'t pop an empty stack';
                break;
        }
        parent::__construct($message, $code, $previous);

    }
}