<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItem;
use Grabbag\Cnst;

/**
 * Resolver items contains values handled by resolver.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class ResolverItems
{

    private $items;

    /**
     * ResolverItems constructor.
     * @param ResolverItem[] $items Array of ResolverItem composing the result from resolver.
     */
    public function __construct($items, $prepare = TRUE)
    {
        $this->items = $prepare ? ResolverItem::prepareResolverItem($items) : $items;
    }

    /**
     * Get item(s) value(s) from items property.
     *
     * If the result contains only one item, it returns value itself.
     * if it contains many it returns an array of values.
     *
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @return array|mixed
     */
    public function getValue($forceArray = false)
    {
        // Recurse items.
        $resultValue = $this->getValueRecurse($this->items);

        return count($resultValue) === 1 && !$forceArray ? $resultValue[0] : $resultValue;
    }

    /**
     * Same as getValue, except it returns ResolverItem instance or ResolverItem instance array.
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @return ResolverItem | ResolverItem[]
     */
    private function getItems($forceArray = false)
    {
        return count($this->items) === 1 && !$forceArray ? $this->items[0] : $this->items;
    }

    /**
     * Recurse an array containing ResolverItem instance and reflect it with each ResolverItem converted in value.
     * @param ResolverItem[] $array Input array containg ResolverItem.
     * @return mixed[]
     * @throws \Exception
     */
    private function getValueRecurse($array)
    {
        $resultArray = [];
        foreach ($array as $key => $arrayItem) {
            if (is_array($arrayItem)) {
                $resultArray[$key] = $this->getValueRecurse($arrayItem);
            } else if ($arrayItem instanceof ResolverItem) {
                $resultArray[$key] = $arrayItem->get();
            } else {
                throw new \Exception('Unexpected type');
            }
        }
        return $resultArray;
    }

    /**
     * Resolve every result items regarding the path or query provided.
     * @param string | string[] $path Path or Query.
     */
    public function resolve($path, $defaultValue = NULL)
    {

        // Prepare
        $pathArray = is_array($path) ? $path : [$path];
        $modifiers = self::prepareModifiers($pathArray);
        $preparedPaths = self::preparePathArray($pathArray);

        // Grab each items
        foreach ($this->items as &$item) {
            $values = $this->resolveEach($item, $preparedPaths, $modifiers, $defaultValue);
            $item = $values;
        }
    }

    /**
     * Resolve one result item regarding the path or Query provided.
     * @param ResolverItem $item Item to be resolved.
     * @param mixed[] $preparedPaths Path or Query.
     * @return ResolverItem[] Resolved items.
     */
    private function resolveEach(ResolverItem $item, $preparedPaths, $modifiers, $defaultValue = NULL)
    {
        // Init Resolver.
        $resolver = new Resolver($item,
            isset($modifiers['default-value']) ? $modifiers['default-value'] : $defaultValue,
            isset($modifiers['exception-enabled']) ? $modifiers['exception-enabled'] : FALSE
        );

        $resultValues = [];
        foreach ($preparedPaths as $preparedPath) {
            $key = $preparedPath['pathObject']->getKey();

            // Resolve the path
            $resolvedItems = $resolver->resolve($preparedPath['pathObject']);

            // Recurse if need.
            if ($preparedPath['pathArray'] !== NULL) {
                $resolvedItems->resolve($preparedPath['pathArray']);
            }

            // Keep only unique if requiered.
            $value = $resolvedItems->getItems();
            if ((isset($modifiers['unique']) && $modifiers['unique'])) {
                $value = self::keepUniqueValuesOnly($value);
            }

            // Transform value
            if (isset($modifiers['transform'])) {
                $value->update(call_user_func_array($modifiers['transform'], [$value->get(), $key]));
            }

            // Transform value
            if (isset($modifiers['debug'])) {
                self::debugVariable($modifiers['debug'], $value->get(), $key);
            }

            // Append value
            if ($key !== NULL && substr($key, 0, 1) !== Cnst::PATH_INTERNAL_ID_CHAR) {
                $resultValues[$key] = $value;
            } else {
                $resultValues[] = $value;
            }
        }

        // Return the very value instead of an array result contains just one single value,
        return count($resultValues) === 1
        && array_keys($resultValues)[0] === 0
        && !(isset($modifiers['keep-array']) && $modifiers['keep-array']) ? $resultValues[0] : $resultValues;
    }

    /**
     * Extract and prepare modifiers from path array.
     * @param array $pathArray User defined path array.
     * @return array
     */
    static private function prepareModifiers($pathArray)
    {
        $modifiers = [];
        foreach ($pathArray as $left => $right) {
            $handlerName = is_integer($left) ? $right : $left;
            $handlerValue = is_integer($left) ? TRUE : $right;
            if (is_string($handlerName) && substr($handlerName, 0, 1) === Cnst::MODIFIER_CHAR) {
                $modifiers[substr($handlerName, 1)] = $handlerValue;
            }
        }
        return $modifiers;
    }

    /**
     * Turns query as defined by user to an internal query ready to be resolved.
     * @param array $pathArray User defined Query.
     * @return array
     */
    static private function preparePathArray($pathArray)
    {
        $preparedPaths = [];
        foreach ($pathArray as $left => $right) {

            $preparedPath = [];

            // Get sub path array
            $preparedPath['pathArray'] = is_integer($left) ? NULL : $right;

            // Get either simple path from left or path with a sub path array from right
            $path = is_integer($left) ? $right : $left;

            // Path is a Path instance already
            if ($path instanceof Path) {
                $preparedPath['pathObject'] = $path;
            } // Path is a string to be instanciated using Path class
            else {

                // Exclude modifiers
                if (substr($path, 0, 1) !== Cnst::MODIFIER_CHAR) {
                    $preparedPath['pathObject'] = new Path($path);
                } else {
                    break;
                }
            }
            $preparedPaths[] = $preparedPath;
        }
        return $preparedPaths;
    }

    /**
     * Implements ?unique modifier behavior : Return an array containing only unique value in array.
     * @param ResolverItem[] $values Array to be filtered.
     * @return ResolverItem[] Result array.
     */
    static private function keepUniqueValuesOnly($values)
    {
        $uniqueValues = [];
        // Unique modifier works only on value returning an array because single value is by definition unique.
        if (is_array($values)) {
            $newValues = [];
            foreach ($values as $key => $valueItem) {

                if (!in_array($valueItem->get(), $uniqueValues)) {
                    $uniqueValues[] = $valueItem->get();
                    if (is_integer($key)) {
                        $newValues[] = $valueItem;
                    } else {
                        $newValues[$key] = $valueItem;
                    }
                }
            }
            return $newValues;
        }
        return $values;
    }

    /**
     * Implement ?debug modifier behavior : Build debug info array and pass it as agument to a callable.
     * @param $callable
     * @param $value
     * @param $key
     */
    static private function debugVariable($callable, $value, $key)
    {
        $debug = [];
        if (is_object($value)) {
            $reflection = new \ReflectionClass($value);
            $debug['class-name'] = $reflection->getName();
            $debug['method'] = get_class_methods($value);
            $debug['object-var'] = array_keys(get_object_vars($value));
        }

        call_user_func_array($callable, [$key, $debug]);
    }
}
