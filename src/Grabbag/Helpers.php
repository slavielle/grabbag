<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 09/09/17
 * Time: 01:50
 */

namespace Grabbag;

use Grabbag\ResolverItems;

/**
 * Helper functions for Grabbag lib.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Helpers
{
    /**
     * Prepare item to normalize it to be an array of ResolverItem.
     * @param ResolverItem | mixed $item
     * @return array|ResolverItem
     */
    public static function prepareResolverItem($item){
        if (is_array($item)) {
            return $item instanceof ResolverItem ? $item : new ResolverItem($item);
        } else {
            return $item instanceof ResolverItem ? [$item] : [new ResolverItem($item)];
        }
    }
}