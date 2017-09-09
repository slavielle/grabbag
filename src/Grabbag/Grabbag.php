<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItems;
use Grabbag\Helpers;

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
        $this->items = Helpers::prepareResolverItem($item);
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
