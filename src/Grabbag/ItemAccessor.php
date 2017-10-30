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
 * Class ItemAccessor
 *
 * ItemAccessor is meant to access a Item instance with :
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

    /**
     * ItemAccessor constructor.
     * @param Item $resolverItem The $resolverItem to be used by the ItemAccessor instance.
     * @throws \Exception
     */
    public function __construct(Item $resolverItem)
    {
        if (!$resolverItem instanceof Item) {
            throw new \Exception('Item instance expected');
        }
        $this->resolverItem = $resolverItem;
    }

    /**
     * Grab apply query to $resolverItem property.
     * @param $query
     * @return $this
     */
    public function grab($query)
    {
        $resolverItems = new ItemCollection(clone $this->resolverItem);
        $value = $resolverItems->resolve($query)->getValue();
        unset($resolverItems);
        return $value;
    }

    /**
     * Get item value.
     * @return mixed
     */
    public function get()
    {
        return $this->resolverItem->get();
    }
}