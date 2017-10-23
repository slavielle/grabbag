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
 * Grabber Allows to resolve value(s) on object chain.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Grabbag
{
    private $items;

    /**
     * Grabbag constructor.
     * @param Item | mixed $item Target Grabbag item.
     */
    function __construct($item)
    {
        $this->items = new ItemCollection($item);
    }

    /**
     * @param string $query Query to resolve.
     * @param mixed $defaultValue Value to return when path resolution fail.
     * @return ItemCollection Items grabbed using path.
     */
    public function resolve($query, $defaultValue = NULL)
    {
        $this->items->resolve($query, $defaultValue);
        return $this->items;
    }

    /**
     * Allow to resolve and get value in one line.
     * @param Item | mixed $item Target Grabbag item.
     * @param string $query Query to resolve.
     * @param mixed $defaultValue Value to return when path resolution fail.
     * @return array|mixed The result value.
     */
    public static function grab($item, $query, $defaultValue = NULL)
    {
        $resolverItems = new ItemCollection($item);
        $resolverItems->resolve($query, $defaultValue);

        $value = $resolverItems->getValue();

        // Optimise memory
        unset($resolverItems);

        return $value;
    }

}
