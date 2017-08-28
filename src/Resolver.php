<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Path;
use slavielle\grabbag\Result;
use slavielle\grabbag\ResolverItem;
use slavielle\grabbag\exceptions\NotAdressableException;
use slavielle\grabbag\exceptions\PropertyNotFoundException;
use slavielle\grabbag\exceptions\UnknownPathKeywordException;

/**
 * Resolver allows to resolve path applied to an item in order to get a result.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class Resolver {

    protected $items;

    /**
     * Constructor.
     * @param ResolverItem | mixed $item
     */
    public function __construct($item) {
        if (is_array($item)) {
            $this->items = $item instanceof ResolverItem ? $item : new ResolverItem($item);
        } else {
            $this->items = $item instanceof ResolverItem ? [$item] : [new ResolverItem($item)];
        }
        $this->pathArray = [];
    }

    /**
     * Resolve path.
     * @param Path $path
     * @return Result
     */
    public function resolve(Path $path) {
        if ($path->isExceptionEnabled()) {
            $items = $this->resolveRecurse($path, $this->items);
        } else {

            try {
                $items = $this->resolveRecurse($path, $this->items);
            } catch (NotAdressableException $e) {
                return new Result([new ResolverItem($path->getDefaultValue())]);
            } catch (PropertyNotFoundException $e) {
                return new Result([new ResolverItem($path->getDefaultValue())]);
            }
        }

        return new Result($items);
    }

    /**
     * Resolve recursively for each PathItem instance in the $path.
     * @param Path $path
     * @param ResolverItem[] $items Items to be resolved.
     * @return type
     */
    private function resolveRecurse(Path $path, $items) {
        if ((NULL !== ($pathItem = $path->next()))) {
            $resultObjects = $this->resolveEach($pathItem, $items);
            return $this->resolveRecurse($path, $resultObjects);
        }
        return $items;
    }

    /**
     * Resolve for each item in items regarding provided path item.
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $items
     * @return type
     * @throws NotAdressableException
     */
    private function resolveEach(PathItem $pathItem, $items) {
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
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param ResolverItem $item
     * @return ResolverItem
     */
    private function resolveSymbol(PathItem $pathItem, ResolverItem $item) {
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
     * Resolve keyword
     * 
     * Get value from item depending on the keyword specified in $pathItem.
     * 
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $item
     * @return type
     */
    private function resolveKeyword(PathItem $pathItem, ResolverItem $item) {
        $resultObjects = [];
        switch ($pathItem->getKey()) {
            case 'each':
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
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $item
     * @return type
     * @throws PropertyNotFoundException
     */
    private function resolveObject(PathItem $pathItem, ResolverItem $item) {

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
        } else {
            throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on item', $pathItem->getKey()));
        }
    }

    /**
     * Resolve item property : Get the value form the item method defined in the $pathItem.
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $item
     * @return type
     */
    private function resolveObjectProperty(PathItem $pathItem, ResolverItem $item) {

        return self::makeResolverItem($item, $item->get()->{$pathItem->getKey()});
    }

    /**
     * Resolve item property : Get the value form the item property defined in the $pathItem.
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $item
     * @param type $prefixWithGet
     * @return type
     */
    private function resolveObjectMethod(PathItem $pathItem, ResolverItem $item, $prefixWithGet = FALSE) {
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
     * @param \slavielle\grabbag\PathItem $pathItem
     * @param type $item
     * @return type
     */
    private function resolveArray(PathItem $pathItem, ResolverItem $item) {

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
    private static function makeResolverItem(ResolverItem $item, $value) {
        $newItem = clone $item;
        $newItem->push($value);
        return $newItem;
    }

}
