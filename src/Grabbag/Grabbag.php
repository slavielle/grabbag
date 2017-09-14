<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItems;

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
     * @param ResolverItem | mixed $item Target Grabbag item.
     */
    function __construct($item)
    {
        $this->items = new ResolverItems($item);
    }

    /**
     * @param string $paths Path to resolve.
     * @return ResolverItems Items grabbed using path.
     */
    public function resolve($paths, $defaultValue = NULL)
    {
        $this->items->resolve($paths, $defaultValue);
        return $this->items;
    }

}
