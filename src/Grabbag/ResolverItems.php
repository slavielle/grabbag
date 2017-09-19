<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItem;
use Grabbag\Cnst;
use Grabbag\SerialResolver;
use Grabbag\exceptions\CantApplyUniqueModifierException;
use Grabbag\exceptions\CantApplyConsiderModifierException;

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
            }
            else if ($arrayItem instanceof ResolverItem) {
                $resultArray[$key] = $arrayItem->get();
            }
            else {
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
        $newItems = [];
        foreach ($this->items as $item) {
            $values = $this->resolveEach($item, $preparedPaths, $modifiers, $defaultValue);
            if (!is_array($values) || count($values) > 0) {
                $newItems[] = $values;
            }
        }
        $this->items = $newItems;

        // Keep only unique if requiered.
        if ((isset($modifiers['unique']) && $modifiers['unique'])) {
            try {
                $this->items = self::keepUniqueValuesOnly($this->items);
            } catch (CantApplyUniqueModifierException $e) {

                // When CantApplyUniqueModifierException are generate in keepUniqueValuesOnly, it's converted in
                // a PHP warning.
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        // Chaining
        return $this;
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
            $pathId = $preparedPath['pathObject']->getPathId();

            // Resolve the path
            $resolvedItems = $resolver->resolve($preparedPath['pathObject']);

            // Recurse if need.
            if ($preparedPath['pathArray'] !== NULL) {
                $resolvedItems->resolve($preparedPath['pathArray']);
            }

            $value = $resolvedItems->getItems();

            // Consider modifier
            $keep = TRUE;
            if (isset($modifiers['consider']) && $pathId !== NULL) {
                if (is_array($value)) {
                    throw new CantApplyConsiderModifierException('Can\'t apply ?consider modifier in a multi-valued path result.');
                }
                $keep = call_user_func_array($modifiers['consider'], [new SerialResolver($value), $pathId]);
                $keep = $keep === NULL ? TRUE : $keep;
            }

            // Value is to be kept.
            if ($keep) {

                // Modifiers are invoqued only on path with id.
                if ($pathId !== NULL) {

                    // Transform modifier
                    if (isset($modifiers['transform'])) {
                        $value->update(call_user_func_array($modifiers['transform'], [$value->get(), $pathId]));
                    }

                    // Debug modifier
                    if (isset($modifiers['debug'])) {
                        self::debugVariable($modifiers['debug'], $value->get(), $pathId);
                    }
                }

                // Append value
                if ($pathId !== NULL && substr($pathId, 0, 1) !== Cnst::PATH_INTERNAL_ID_CHAR) {
                    $resultValues[$pathId] = $value;
                }
                else {
                    $resultValues[] = $value;
                }
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
                }
                else {
                    break;
                }
            }
            $preparedPaths[] = $preparedPath;
        }
        return $preparedPaths;
    }

    /**
     * Implements ?unique modifier behavior : Return an array containing only unique value in array.
     * @param ResolverItem[] $items Array to be filtered.
     * @maram integer $recurseLevel. Level of recursion.
     * @return ResolverItem[] Result array.
     */
    static private function keepUniqueValuesOnly($items, $recurseLevel = 0)
    {
        $uniqueValues = [];

        // Unique modifier works only on value returning an array because single value is by definition unique.
        if (is_array($items)) {
            $newValues = [];
            foreach ($items as $key => $item) {

                // $items should contains a list of ResolverItem instance. if one item
                if (!$item instanceof ResolverItem) {

                    // Items is normally a list of ResolverItem instances, but, in some cases, $items is an array
                    // containing one element containing a list of ResolverItem instances. In this case we just recurse
                    // only one level up.
                    if (count($items) === 1 && is_integer($key) && $recurseLevel === 0) {
                        $newValues[] = self::keepUniqueValuesOnly($item, $recurseLevel + 1);
                        break;
                    }

                    // If process reach this point it means the $items array cannot apply unique modifier. In this case
                    // we throw an exception that will be converted in PHP warning.
                    else {

                        //@todo identify each case in order to give the end user more information about what's going on.
                        throw new CantApplyUniqueModifierException('Unable to apply ?unique modifier on this result scope.');
                    }
                }

                //Preserve unique values only.
                $itemValue = $item->get();
                if (!in_array($itemValue, $uniqueValues)) {
                    $uniqueValues[] = $itemValue;
                    if (is_integer($key)) {
                        $newValues[] = $item;
                    }
                    else {
                        $newValues[$key] = $item;
                    }
                }
            }
            return $newValues;
        }
        return $items;
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
