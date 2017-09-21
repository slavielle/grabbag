<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 20/09/17
 * Time: 18:01
 */

namespace Grabbag;

/**
 * Class VoidDefaultValue
 * @package Grabbag
 */
class VoidDefaultValue
{
    private $fallbackDefaultValue;

    public function __construct($fallbackDefaultValue = NULL)
    {
        $this->fallbackDefaultValue = $fallbackDefaultValue;
    }

    public function getFallbackDefaultValue(){
        return $this->fallbackDefaultValue;
    }
}