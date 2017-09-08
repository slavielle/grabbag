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

    
    function __construct($item){
        if (is_array($item)) {
            $this->items = $item instanceof ResolverItem ? $item : new ResolverItem($item);
        } else {
            $this->items = $item instanceof ResolverItem ? [$item] : [new ResolverItem($item)];
        }
    }

    /**
     * @param string $paths Path to resolve.
     * @return ResolverItems Items grabbed using path.
     */
    public function resolve($paths, $defaultValue = NULL)
    {
        $items = new ResolverItems($this->items);
        $items->resolve($paths, $defaultValue);
        return $items;
    }

}
