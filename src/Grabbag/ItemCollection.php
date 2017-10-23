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

use Grabbag\exceptions\ModifierException;
use Grabbag\exceptions\ModifierInternalException;

/**
 * Resolver items contains values handled by resolver.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class ItemCollection
{

    private $items;
    private $forceArray;

    /**
     * ItemCollection constructor.
     * @param Item|Item[] $items Array of Item (or single item) composing the result from resolver.
     */
    public function __construct($items, $prepare = TRUE)
    {
        $this->forceArray = FALSE;
        $this->items = $prepare ? Item::prepareResolverItem($items) : $items;
    }

    /**
     * Set $forceArray property.
     * @param  bool $forceArray
     */
    public function setForceArray($forceArray)
    {
        $this->forceArray = $forceArray;
    }

    /**
     * Get item(s) value(s) from $items property.
     *
     * If the result contains only one item, it returns value itself.
     * if it contains many it returns an array of values.
     *
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @return array|mixed
     */
    public function getValue($forceArray = FALSE)
    {
        return $this->getItems($forceArray, TRUE);
    }

    /**
     * Get item(s) instance or item values from $items property.
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @param bool $extractValues Is TRUE, will use Item value(s) instead of Item instance(s) in result .
     * @return Item | Item[] | mixed | mixed[] items array
     */
    private function getItems($forceArray = FALSE, $extractValues = FALSE)
    {
        $forceArray = TRUE;
        if ($extractValues) {
            $resultValue = $this->getValueRecurse($this->items);
        }
        else {
            $resultValue = $this->items;
        }
        return count($resultValue) === 1 && !($forceArray && $this->forceArray) ? $resultValue[0] : $resultValue;
    }

    /**
     * Recurse an array containing Item instance and reflect it with each Item converted in value.
     * @param Item[] $array Input array containg Item.
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
            else if ($arrayItem instanceof Item) {
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
     * @param mixed $defaultValue Default value to provide in case the path resolution fails.
     */
    public function resolve($path, $defaultValue = NULL, $exceptionEnabled = FALSE)
    {
        // Prepare stuff.
        $pathArray = is_array($path) ? $path : [$path];
        $modifiers = self::prepareModifiers($pathArray);
        $preparedPaths = self::preparePathArray($pathArray);

        // Grab each items.
        $newItems = [];
        foreach ($this->items as $item) {
            $values = $this->resolveEach($item, $preparedPaths, $modifiers, $defaultValue, $exceptionEnabled);
            if (!is_array($values) || count($values) > 0) {
                $newItems[] = $values;
            }
        }
        $this->items = $newItems;

        // Keep only unique if requiered.
        if (($modifiers->exists('unique') && $modifiers->getDefault('unique'))) {
            try {
                $this->items = self::keepUniqueValuesOnly($this->items);
            } catch (ModifierInternalException $e) {

                // When ModifierInternalException is generated in keepUniqueValuesOnly,
                // it's converted in a PHP warning.
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        // Chaining pattern.
        return $this;
    }

    /**
     * Resolve one result item regarding the path or Query provided.
     * @param Item $item Item to be resolved.
     * @param mixed[] $preparedPaths Path or Query.
     * @param Modifiers $modifiers Prepared modifiers.
     * @param mixed $defaultValue t
     * @param bool $exceptionEnabled
     * @return Item[] Resolved items.
     * @throws ModifierException
     */
    private function resolveEach(Item $item, $preparedPaths, Modifiers $modifiers, $defaultValue = NULL, $exceptionEnabled = FALSE)
    {
        $exceptionEnabled = $modifiers->exists('exception-enabled') ? $modifiers->getDefault('exception-enabled') : $exceptionEnabled;

        // Init Resolver.
        $resolver = new Resolver($item,
            NULL,
            $exceptionEnabled
        );

        //Resolving loop.
        $beforeModifiersValues = [];
        foreach ($preparedPaths as $preparedPath) {

            $pathId = $preparedPath['pathObject']->getPathId();

            $resolver->setDefaultValue($modifiers->exists('default-value') ? $modifiers->get('default-value', $pathId) : $defaultValue);

            // Resolve the path
            $resolvedItems = $resolver->resolve($preparedPath['pathObject']);

            // Recurse if need.
            if ($preparedPath['pathArray'] !== NULL) {
                $resolvedItems->resolve($preparedPath['pathArray'], NULL, $exceptionEnabled);
            }

            $value = $resolvedItems->getItems();

            if ($pathId !== NULL) {
                $beforeModifiersValues[$pathId] = $value;
            }
            else {
                $beforeModifiersValues[] = $value;
            }

        }

        // Prepare limited accessors for every path value in the path-array.
        $beforeModifiersValueAccessors = [];
        foreach ($beforeModifiersValues as $key => $beforeModifiersValue) {

            if ($beforeModifiersValue instanceof Item) {
                $itemAccessor = new ItemAccessor($beforeModifiersValue);
            }
            else {
                // Multi-valued paths are not supported.
                $itemAccessor = NULL;
            }

            if (NULL !== ($pathId = is_integer($key) ? NULL : $key)) {
                $beforeModifiersValueAccessors[$pathId] = $itemAccessor;
            }
            else {
                $beforeModifiersValueAccessors[] = $itemAccessor;
            }
        }

        //After-resolving modifiers loop.
        foreach ($beforeModifiersValues as $key => $beforeModifiersValue) {

            $pathId = is_integer($key) ? NULL : $key;

            // Transform modifier
            // Restriction : Transform modifier cannot be called on multi-valued path (if $beforeModifiersValue is an array and not an
            // instance of Item).
            if ($modifiers->exists('transform') && $beforeModifiersValue instanceof Item) {
                $beforeModifiersValue->update(call_user_func_array(
                        $modifiers->get('transform', $pathId),
                        [$beforeModifiersValue->get(), $pathId, new ItemAccessor($beforeModifiersValue), $beforeModifiersValueAccessors]
                    )
                );
            }

            // Call modifier
            // Restriction : Call modifier cannot be called on multi-valued path (if $beforeModifiersValue is an array and not an
            // instance of Item).
            if ($modifiers->exists('call') && $beforeModifiersValue instanceof Item) {
                call_user_func_array(
                    $modifiers->get('call', $pathId),
                    [$beforeModifiersValue->get(), $pathId, new ItemAccessor($beforeModifiersValue), $beforeModifiersValueAccessors]
                );
            }

            // Debug modifier
            if ($modifiers->exists('debug')) {
                self::debugVariable(
                    $modifiers->get('debug', $pathId),
                    $beforeModifiersValue->get(), $pathId);
            }
        }

        // Keep loop.
        $resultValues = [];
        foreach ($beforeModifiersValues as $key => $beforeModifiersValue) {
            $pathId = is_integer($key) ? NULL : $key;

            // Consider modifier
            $keep = TRUE;
            if ($modifiers->exists('consider') && $pathId !== NULL) {
                if (is_array($beforeModifiersValue)) {
                    throw new ModifierException(ModifierException::ERR_1);
                }
                $keep = call_user_func_array(
                    $modifiers->get('consider', $pathId),
                    [new ItemAccessor($beforeModifiersValue), $pathId]
                );

                // NULL returned by callback means keep.
                $keep = $keep === NULL ? TRUE : $keep;
            }

            // Value is to be kept.
            if ($keep) {

                // Append value
                if ($pathId !== NULL && substr($pathId, 0, 1) !== Cnst::PATH_INTERNAL_ID_CHAR) {
                    $resultValues[$pathId] = $beforeModifiersValue;
                }
                else {
                    $resultValues[] = $beforeModifiersValue;
                }
            }
        }

        // Return a single value instead of an array containing just one single value, in following circumstance ...
        $returnSingleValue =

            // if there is only one result,
            count($resultValues) === 1

            // and if there is ony one path in the path-array,
            && count($preparedPaths) === 1

            // and if the single result has a numeric index (not a key),
            && array_keys($resultValues)[0] === 0

            // and if there's no keep-array modifier in the path-array,
            && !($modifiers->exists('keep-array') && $modifiers->getDefault('keep-array'));

        return $returnSingleValue ? $resultValues[0] : $resultValues;
    }

    /**
     * Extract and prepare modifiers from path-array.
     * @param array $pathArray User defined path-array.
     * @return Modifiers
     */
    static private function prepareModifiers($pathArray)
    {


        $modifiers = new Modifiers();
        foreach ($pathArray as $left => $right) {
            $handlerName = (int)$left === $left ? $right : $left;
            $handlerValue = (int)$left === $left ? TRUE : $right;
            $modifiers->submit($handlerName, $handlerValue);
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

            // Get sub path-array.
            $preparedPath['pathArray'] = (int)$left === $left ? NULL : $right;

            // Get either simple path from left or path with a sub path-array from right
            $path = (int)$left === $left ? $right : $left;


            // Path is a Path instance already
            if ($path instanceof Path) {
                $preparedPath['pathObject'] = $path;
            }

            // Path is a string to be instantiated using Path class
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
     * @param Item[] $items Array to be filtered.
     * @param integer $recurseLevel . Level of recursion.
     * @return Item[] Result array.
     * @throws ModifierInternalException
     */
    static private function keepUniqueValuesOnly($items, $recurseLevel = 0)
    {
        $uniqueValues = [];

        // Unique modifier works only on value returning an array because single value is by definition unique.
        if (is_array($items)) {
            $newValues = [];
            foreach ($items as $key => $item) {

                // $items should contains a list of Item instance. if one item
                if (!$item instanceof Item) {

                    // Items is normally a list of Item instances, but, in some cases, $items is an array
                    // containing one element containing a list of Item instances. In this case we just recurse
                    // only one level up.
                    if (count($items) === 1 && (int)$key === $key && $recurseLevel === 0) {
                        $newValues[] = self::keepUniqueValuesOnly($item, $recurseLevel + 1);
                        break;
                    }

                    // If process reach this point it means the $items array cannot apply unique modifier. In this case
                    // we throw an exception that will be converted in PHP warning.
                    else {

                        //@todo identify each case in order to give the end user more information about what's going on.
                        throw new ModifierInternalException(ModifierInternalException::ERR_1);
                    }
                }

                //Preserve unique values only.
                $itemValue = $item->get();
                if (!in_array($itemValue, $uniqueValues)) {
                    $uniqueValues[] = $itemValue;
                    if ((int)$key === $key) {
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
