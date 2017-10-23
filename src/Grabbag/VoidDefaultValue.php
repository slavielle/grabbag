<?php

/*
 * This file is part of the Grabbag package.
 *
 * (c) Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    public function getFallbackDefaultValue()
    {
        return $this->fallbackDefaultValue;
    }
}