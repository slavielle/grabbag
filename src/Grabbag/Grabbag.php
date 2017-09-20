<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ItemCollection;

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
     * @param string $paths Path to resolve.
     * @param mixed $defaultValue Value to return when path resolution fail.
     * @return ItemCollection Items grabbed using path.
     */
    public function resolve($paths, $defaultValue = NULL)
    {
        $this->items->resolve($paths, $defaultValue);
        return $this->items;
    }

    /**
     * Allow to resolve and get value in one line.
     * @param Item | mixed $item Target Grabbag item.
     * @param string $paths Path to resolve.
     * @param mixed $defaultValue Value to return when path resolution fail.
     * @return array|mixed The result value.
     */
    public static function grab($item, $paths, $defaultValue = NULL){
        $resolverItems = new ItemCollection($item);
        $resolverItems->resolve($paths, $defaultValue);

        return $resolverItems->getValue();
    }

}
