<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 18/09/17
 * Time: 15:43
 */

namespace Grabbag;

use Grabbag\ResolverItem;

/**
 * Class SerialResolver
 *
 * SerialResolver is meant to resolve a ResolverItem several times form the same start value.
 *
 * NB : a ResolverItem instance changes its value when you resolve it : its value move from the start value to the end
 * value resolving led it to. When a resolveItem is resolved, it's kind of "burned" somehow.
 * If you want to be able to resolve a ResolverItem many times from the same start value, you
 * have to use the serial resolver. SerialResolver allows to resolve a $resolverItem letting him untouched.
 *
 * @package Grabbag
 */
class SerialResolver
{
    private $resolverItem;
    private $allowStringPathOnly;

    /**
     * SerialResolver constructor.
     * @param ResolverItem $resolverItem The $resolverItem to be used by the SerialResolver instance.
     */
    public function __construct(ResolverItem $resolverItem, $allowStringPathOnly = FALSE)
    {
        if (!$resolverItem instanceof ResolverItem) {
            throw new \Exception('ResolverItem instance expected');
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
        $resolverItems = new ResolverItems(clone $this->resolverItem);
        return $resolverItems->resolve($path);
    }

    public function get()
    {
        return $this->resolverItem->get();
    }
}