<?php

namespace Grabbag;

use Grabbag\Path;
use Grabbag\Item;
use Grabbag\ItemCollection;
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
     * @param Item | mixed $item
     */
    public function __construct($item, $defaultValue = NULL, $exceptionEnabled = FALSE)
    {
        $this->items = Item::prepareResolverItem($item);
        $this->defaultValue = $defaultValue;
        $this->exceptionEnabled = $exceptionEnabled;
    }

    /**
     * Resolve path.
     * @param Path $path Path to resolve.
     * @return ItemCollection Resolved items as a result.
     */
    public function resolve(Path $path)
    {
        $path->rewind();
        if ($this->exceptionEnabled) {
            $items = $this->resolveRecurse($path, $this->items);
        }
        else {
            try {
                $items = $this->resolveRecurse($path, $this->items);
            } catch (NotAdressableException $e) {
                return new ItemCollection([new Item($this->defaultValue)], FALSE);
            } catch (PropertyNotFoundException $e) {
                return new ItemCollection([new Item($this->defaultValue)], FALSE);
            }
        }

        return new ItemCollection($items, FALSE);
    }


    /**
     * Resolve recursively for each PathItem instance in the $path.
     * @param Path $path Path to resolve.
     * @param Item[] $items Items to be resolved.
     * @return Item[] A set of resolved items.
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
     * @param Item[] $items Item to be resolved.
     * @return Item[] A set of resolved items.
     * @throws NotAdressableException
     */
    private function resolveEach(PathItem $pathItem, $items)
    {
        $resultObjects = [];
        foreach ($items as $item) {
            if ($pathItem->isKeyword()) {
                $resultObjects = array_merge($resultObjects, $this->resolveKeyword($pathItem, $item));
            }
            else if ($pathItem->isSymbol()) {
                $resultObjects[] = $this->resolveSymbol($pathItem, $item);
            }
            else if (is_object($item->get())) {
                $resultObjects[] = $this->resolveObject($pathItem, $item);
            }
            else if (is_array($item->get())) {
                $resultObjects[] = $this->resolveArray($pathItem, $item);
            }
            else {
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
     * @param Item $item Item to be resolved.
     * @return Item Resolved Item.
     */
    private function resolveSymbol(PathItem $pathItem, Item $item)
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
     * @param Item $item Item to be resolved.
     * @throws UnknownPathKeywordException if a keyword is unknown.
     * @return $resultObjects[] A set of Resolved Item.
     */
    private function resolveKeyword(PathItem $pathItem, Item $item)
    {
        $resultObjects = [];
        switch ($pathItem->getKey()) {
            case 'any':
                foreach ($item->get() as $key=>$entry) {
                    $resultObjects[] = self::makeResolverItem($item, $entry, $key);
                }
                break;
            case 'key':
                $resultObjects[] = self::makeResolverItem($item, $item->getKey());
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
     * @param Item $item Item to be resolved.
     * @return Item Resolved Item.
     * @throws PropertyNotFoundException
     */
    private function resolveObject(PathItem $pathItem, Item $item)
    {

        // Test item property
        if (isset($item->get()->{$pathItem->getKey()})) {
            return $this->resolveObjectProperty($pathItem, $item);
        }

        // Test if method exists with its key name.
        else if (method_exists($item->get(), $pathItem->getKey())) {
            return $this->resolveObjectMethod($pathItem, $item);
        }

        // Test if method exists with "get" + its capitalized key name.
        else if (method_exists($item->get(), 'get' . ucfirst($pathItem->getKey()))) {
            return $this->resolveObjectMethod($pathItem, $item, TRUE);
        }

        // Throw exception : None of the previous solutions worked.
        else {
            throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on item', $pathItem->getKey()));
        }
    }

    /**
     * Resolve item property : Get the value form the item method defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @return Item Resolved Item.
     */
    private function resolveObjectProperty(PathItem $pathItem, Item $item)
    {
        return self::makeResolverItem($item, $item->get()->{$pathItem->getKey()}, $pathItem->getKey());
    }

    /**
     * Resolve item property : Get the value form the item property defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @param bool $prefixWithGet Try to find a method name using path item key prefixed with 'get'.
     * @throws NotAdressableException if method call throw an exception.
     * @return Item Resolved Item.
     */
    private function resolveObjectMethod(PathItem $pathItem, Item $item, $prefixWithGet = FALSE)
    {
        $params = [];
        if ($pathItem->hasParam()) {
            $params = $pathItem->getParams();
        }

        try {
            $value = call_user_func_array([$item->get(), $prefixWithGet ? 'get' . ucfirst($pathItem->getKey()) : $pathItem->getKey()], $params);
        } catch (\Exception $e) {
            throw new NotAdressableException('Parameters passed to method throw an exception');
        }

        return self::makeResolverItem($item, $value);
    }

    /**
     * Resolve array : Get the value form the array key defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @throws PropertyNotFoundException if property cant be found in $item.
     * @return Item Resolved Item.
     */
    private function resolveArray(PathItem $pathItem, Item $item)
    {

        if (isset($item->get()[$pathItem->getKey()])) {
            $value = $item->get()[$pathItem->getKey()];
            return self::makeResolverItem($item, $value, $pathItem->getKey());
        }
        throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on array', $pathItem->getKey()));
    }

    /**
     * Make new resolver item.
     * @param Item $item Previous level item.
     * @param mixed $value New item value.
     * @param string|integer $key Key value is the value provide from a array or an object property.
     * @return Item
     */
    private static function makeResolverItem(Item $item, $value, $key = NULL)
    {
        $newItem = clone $item;
        $newItem->push($value, $key);
        return $newItem;
    }

}
