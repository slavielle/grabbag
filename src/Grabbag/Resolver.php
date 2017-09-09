<?php

namespace Grabbag;

use Grabbag\Path;
use Grabbag\ResolverItem;
use Grabbag\ResolverItems;
use Grabbag\exceptions\NotAdressableException;
use Grabbag\exceptions\PropertyNotFoundException;
use Grabbag\exceptions\UnknownPathKeywordException;

/**
 * Resolver allows to resolve path applied to an item in order to get a result.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Resolver
{

    protected $items;
    protected $defaultValue;
    protected $exceptionEnabled;

    /**
     * Constructor.
     * @param ResolverItem | mixed $item
     */
    public function __construct($item, $defaultValue = NULL, $exceptionEnabled = FALSE)
    {
        $this->items = Helpers::prepareResolverItem($item);
        $this->pathArray = [];
        $this->defaultValue = $defaultValue;
        $this->exceptionEnabled = $exceptionEnabled;
    }

    /**
     * Resolve path.
     * @param Path $path Path to resolve.
     * @return ResolverItems Resolved items as a result.
     */
    public function resolve(Path $path)
    {
        if ($this->exceptionEnabled) {
            $items = $this->resolveRecurse($path, $this->items);
        } else {
            try {
                $items = $this->resolveRecurse($path, $this->items);
            } catch (NotAdressableException $e) {
                return new ResolverItems([new ResolverItem($this->defaultValue)]);
            } catch (PropertyNotFoundException $e) {
                return new ResolverItems([new ResolverItem($this->defaultValue)]);
            }
        }

        return new ResolverItems($items);
    }


    /**
     * Resolve recursively for each PathItem instance in the $path.
     * @param Path $path Path to resolve.
     * @param ResolverItem[] $items Items to be resolved.
     * @return ResolverItem[] A set of resolved items.
     */
    private function resolveRecurse(Path $path, $items)
    {
        if ((NULL !== ($pathItem = $path->next()))) {
            $resultObjects = $this->resolveEach($pathItem, $items);
            return $this->resolveRecurse($path, $resultObjects);
        }
        return $items;
    }

    /**
     * Resolve for each item in $items regarding provided path item.
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem[] $items Item to be resolved.
     * @return ResolverItem[] A set of resolved items.
     * @throws NotAdressableException
     */
    private function resolveEach(PathItem $pathItem, $items)
    {
        $resultObjects = [];
        foreach ($items as $item) {
            if ($pathItem->isKeyword()) {
                $resultObjects = array_merge($resultObjects, $this->resolveKeyword($pathItem, $item));
            } else if ($pathItem->isSymbol()) {
                $resultObjects[] = $this->resolveSymbol($pathItem, $item);
            } else if (is_object($item->get())) {
                $resultObjects[] = $this->resolveObject($pathItem, $item);
            } else if (is_array($item->get())) {
                $resultObjects[] = $this->resolveArray($pathItem, $item);
            } else {
                throw new NotAdressableException('Can\'t resolve');
            }
        }
        return $resultObjects;
    }

    /**
     * Resolve symbol.
     *
     * Symbol are special path item such as '.' or '..'
     *
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @return ResolverItem Resolved Item.
     */
    private function resolveSymbol(PathItem $pathItem, ResolverItem $item)
    {
        switch ($pathItem->getKey()) {
            case '.':
                return clone $item;
            case '..':
                $newItem = clone $item;
                $newItem->pop();
                return $newItem;
        }
    }

    /**
     * Resolve keyword.
     *
     * Get value from item depending on the keyword specified in $pathItem.
     *
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @throws UnknownPathKeywordException if a keyword is unknown.
     * @return $resultObjects[] A set of Resolved Item.
     */
    private function resolveKeyword(PathItem $pathItem, ResolverItem $item)
    {
        $resultObjects = [];
        switch ($pathItem->getKey()) {
            case 'any':
                foreach ($item->get() as $entry) {
                    $resultObjects[] = self::makeResolverItem($item, $entry);
                }
                break;

            // To be continued on future needs.

            default :
                throw new UnknownPathKeywordException(sprintf('Unknown keyword "#%s" in path', $pathItem->getKey()));
        }
        return $resultObjects;
    }

    /**
     * Resolve item regarding $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @return ResolverItem Resolved Item.
     * @throws PropertyNotFoundException
     */
    private function resolveObject(PathItem $pathItem, ResolverItem $item)
    {

        // Test item property
        if (isset($item->get()->{$pathItem->getKey()})) {
            return $this->resolveObjectProperty($pathItem, $item);
        } // Test if method exists with its key name.
        else if (method_exists($item->get(), $pathItem->getKey())) {
            return $this->resolveObjectMethod($pathItem, $item);
        } // Test if method exists with "get" + its capitalized key name.
        else if (method_exists($item->get(), 'get' . ucfirst($pathItem->getKey()))) {
            return $this->resolveObjectMethod($pathItem, $item, TRUE);
        } else {
            throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on item', $pathItem->getKey()));
        }
    }

    /**
     * Resolve item property : Get the value form the item method defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @return ResolverItem Resolved Item.
     */
    private function resolveObjectProperty(PathItem $pathItem, ResolverItem $item)
    {
        return self::makeResolverItem($item, $item->get()->{$pathItem->getKey()});
    }

    /**
     * Resolve item property : Get the value form the item property defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @param bool $prefixWithGet Try to find a method name using path item key prefixed with 'get'.
     * @throws NotAdressableException if method call throw an exception.
     * @return ResolverItem Resolved Item.
     */
    private function resolveObjectMethod(PathItem $pathItem, ResolverItem $item, $prefixWithGet = FALSE)
    {
        $params = [];
        if ($pathItem->hasParam()) {
            $params = $pathItem->getParams();
        }

        try {
            $value = call_user_method_array($prefixWithGet ? 'get' . ucfirst($pathItem->getKey()) : $pathItem->getKey(), $item->get(), $params);
        } catch (\Exception $e) {
            throw new NotAdressableException('Parameters passed to method throw an exception');
        }

        return self::makeResolverItem($item, $value);
    }

    /**
     * Resolve array : Get the value form the array key defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param ResolverItem $item Item to be resolved.
     * @throws PropertyNotFoundException if property cant be found in $item.
     * @return ResolverItem Resolved Item.
     */
    private function resolveArray(PathItem $pathItem, ResolverItem $item)
    {

        if (isset($item->get()[$pathItem->getKey()])) {
            $value = $item->get()[$pathItem->getKey()];
            return self::makeResolverItem($item, $value);
        }
        throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on array', $pathItem->getKey()));
    }

    /**
     * Make new resolver item.
     * @param ResolverItem $item Previous level item.
     * @param type $value New item value.
     * @return ResolverItem
     */
    private static function makeResolverItem(ResolverItem $item, $value)
    {
        $newItem = clone $item;
        $newItem->push($value);
        return $newItem;
    }

}
