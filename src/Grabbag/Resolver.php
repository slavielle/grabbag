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

use Grabbag\exceptions\PathException;
use Grabbag\exceptions\ResolverException;

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
     * @var Path $appliedPath Path applied to resolver.
     */
    protected $appliedPath;

    /**
     * Constructor.
     * @param mixed $defaultValue Default value if resolver can't be resolved.
     * @param bool $exceptionEnabled If exception enabled ResolverException are thrown by resolver.
     * @param Item | mixed $item
     */
    public function __construct($item, $defaultValue = NULL, $exceptionEnabled = FALSE)
    {

        $this->items = Item::normalizeResolverItem($item);
        $this->exceptionEnabled = $exceptionEnabled;
        $this->setDefaultValue($defaultValue);
    }

    /**
     * Setter for defaultValue property.
     * @param mixed $defaultValue New default value;
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue === NULL ? new VoidDefaultValue() : ($defaultValue instanceof NullDefaultValue ? NULL : $defaultValue);
    }

    /**
     * Resolve path.
     * @param Path $path Path to resolve.
     * @return ItemCollection Resolved items as a result.
     */
    public function resolve(Path $path)
    {
        $path->rewind();

        $this->appliedPath = $path;

        $itemCollection = new ItemCollection($this->resolveRecurse($path, $this->items), FALSE);

        // If a path is meant to match multiple values, we must force array when output.
        $itemCollection->setForceArray($path->isMutipleMatching());

        return $itemCollection;
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
     * @throws ResolverException
     */
    private function resolveEach(PathItem $pathItem, $items)
    {
        $allResultObjects = [];
        foreach ($items as $item) {

            $resultObjects = $this->catchExceptionIfNeed(function () use ($item, $pathItem) {
                if ($pathItem->isKeyword()) {
                    $resultObjects = $this->resolveKeyword($pathItem, $item);
                }
                else if ($pathItem->isSymbol()) {
                    $resultObjects = $this->resolveSymbol($pathItem, $item);
                }
                else if (is_object($item->get())) {
                    $resultObjects = $this->resolveObject($pathItem, $item);
                }
                else if (is_array($item->get())) {
                    $resultObjects = $this->resolveArray($pathItem, $item);
                }
                else {
                    throw new ResolverException(ResolverException::ERR_1);
                }

                return $resultObjects;
            });

            // The path item returned a array (multi-matching path item).
            if (is_array($resultObjects)) {
                $allResultObjects = array_merge($allResultObjects, $resultObjects);
            }

            // The path item returned non-void item.
            else if (!$resultObjects->get() instanceof VoidDefaultValue) {
                $allResultObjects[] = $resultObjects;
            }

            // The path item returned void item on a single-matching path item.
            else if (!$this->appliedPath->isMutipleMatching()) {
                $allResultObjects[] = $this->makeResolverItem($item, $resultObjects->get()->getFallbackDefaultValue());
            }

            // In other case (void item on a multiple-matching path item $resultObjects is not meant to be recorded.

        }
        return $allResultObjects;
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
        return NULL;
    }

    /**
     * Resolve keyword.
     *
     * Get value from item depending on the keyword specified in $pathItem.
     *
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @return Item[] A set of Resolved item.
     * @throws PathException If a keyword is unknown.
     * @throws ResolverException.
     */
    private function resolveKeyword(PathItem $pathItem, Item $item)
    {

        PathItem::requireKeywordExists($pathItem->getKey());

        $resultObjects = [];

        switch ($pathItem->getKey()) {
            case 'any':
                if (is_array($item->get()) || is_object($item->get())) {
                    foreach ($item->get() as $key => $entry) {
                        $resultObjects[] = self::makeResolverItem($item, $entry, $key);
                    }
                }
                else {
                    throw new ResolverException(ResolverException::ERR_2);
                }
                break;
            case 'key':
                $resultObjects[] = self::makeResolverItem($item, $item->getKey());
                break;

            // To be continued for future needs.

            default :
                throw new PathException(PathException::ERR_4, [$pathItem->getKey()]);
        }
        return $resultObjects;

    }

    /**
     * Resolve item regarding $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @return Item Resolved item.
     * @throws ResolverException
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
            throw new ResolverException(ResolverException::ERR_4, [$pathItem->getKey()]);
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
     * @throws ResolverException if method call throw an exception.
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
            throw new ResolverException(ResolverException::ERR_3);
        }

        return self::makeResolverItem($item, $value);
    }

    /**
     * Resolve array : Get the value form the array ($item) defined in the $pathItem.
     * @param PathItem $pathItem Path item to resolve.
     * @param Item $item Item to be resolved.
     * @throws ResolverException if property cant be found in $item.
     * @return Item Resolved Item.
     */
    private function resolveArray(PathItem $pathItem, Item $item)
    {
        if (isset($item->get()[$pathItem->getKey()])) {
            $value = $item->get()[$pathItem->getKey()];
            return self::makeResolverItem($item, $value, $pathItem->getKey());
        }
        throw new ResolverException(ResolverException::ERR_5, [$pathItem->getKey()]);
    }

    /**
     * Make new Item instance by cloning a parent Item instance and pushing info into it.
     * @param Item $item Previous level item instance.
     * @param mixed $value New item value.
     * @param string|integer $key Key value from an array (key) or an object (property).
     * @return Item New Item.
     */
    private static function makeResolverItem(Item $item, $value, $key = NULL)
    {
        $newItem = clone $item;
        $newItem->push($value, $key);
        return $newItem;
    }

    /**
     * Run the items-producing callable and catch some exceptions that eligible to produce default value.
     * @param $callable Items-producing callable.
     * @return Item|Item[] the produced items or the the default value.
     */
    private function catchExceptionIfNeed($callable)
    {
        if ($this->exceptionEnabled) {
            $items = $callable();
        }
        else {
            try {
                $items = $callable();
            } catch (ResolverException $e) {
                $items = new Item($this->defaultValue);
            }
        }

        return $items;
    }

}
