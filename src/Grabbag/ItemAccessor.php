<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 18/09/17
 * Time: 15:43
 */

namespace Grabbag;

use Grabbag\Item;

/**
 * Class ItemAccessor
 *
 * ItemAccessor is meant to access a Item with
 *
 * - a restricted set of methods (get, grab)
 * - a grab method allowing to resolve the Item several times without "burning" it.
 *
 * NB : a Item instance changes its value when you resolve it : its value move from the start value to the end
 * value resolving led it to. When a resolveItem is resolved, it's kind of "burned" somehow.
 * If you want to be able to resolve a Item many times from the same start value, you
 * have to use the serial resolver. ItemAccessor allows to resolve a $resolverItem letting him untouched.
 *
 * @package Grabbag
 */
class ItemAccessor
{
    private $resolverItem;
    private $allowStringPathOnly;

    /**
     * ItemAccessor constructor.
     * @param Item $resolverItem The $resolverItem to be used by the ItemAccessor instance.
     */
    public function __construct(Item $resolverItem, $allowStringPathOnly = FALSE)
    {
        if (!$resolverItem instanceof Item) {
            throw new \Exception('Item instance expected');
        }
        $this->resolverItem = $resolverItem;
        $this->allowStringPathOnly = $allowStringPathOnly;
    }

    /**
     * @param $path
     * @return $this
     */
    public function grab($path)
    {
        $resolverItems = new ItemCollection(clone $this->resolverItem);
        return $resolverItems->resolve($path);
    }

    public function get()
    {
        return $this->resolverItem->get();
    }
}