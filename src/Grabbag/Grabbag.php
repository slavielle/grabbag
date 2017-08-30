<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItems;

/**
 * Grabber Allows to grab value(s) on object chain.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Grabbag extends Resolver
{
    /**
     * @param string $paths Path to resolve.
     * @return ResolverItems Items grabbed using path.
     */
    public function grab($paths)
    {
        $items = new ResolverItems($this->items, NULL);
        $items->grab($paths);
        return $items;
    }

}
