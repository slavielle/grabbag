<?php

namespace Grabbag;

use Grabbag\Resolver;
use Grabbag\ResolverItem;
use Grabbag\Cnst;

/**
 * Result implements resolve process result.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class ResolverItems
{

    private $items;

    /**
     * Result constructor.
     * @param ResolverItem[] $items Array of ResolverItem composing the result from resolver.
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * Get item(s) value() from result.
     *
     * If the result contains only one item, it returns value itself.
     * if it contains many it returns an array of values.
     *
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @return array|mixed
     */
    public function getValue($forceArray = false)
    {

        $resultValue = $this->getValueRecurse($this->items);
        return count($resultValue) === 1 && !$forceArray ? $resultValue[0] : $resultValue;
    }

    /**
     * Same as getValue, except it returns ResolverItem instance or instance array.
     * @param bool $forceArray Force the method result to be an array even if there is only one result item.
     * @return ResolverItem | ResolverItem[]
     */
    private function getRawValue($forceArray = false)
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
     * Perform a each on all result value and fire a callback function passing it as argument.
     * @param callable $callable Function to fire.
     */
    public function each($callable)
    {
        foreach ($this->items as $item) {
            $callable($item->get());
        }
    }

    /**
     * Perform a each on all result value and fire a callback function passing it as argument,
     * and update the result value using the function result.
     * @param callable $callable Function to fire.
     * @return Result Chaining
     */
    public function transformEach($callable)
    {
        foreach ($this->items as $item) {
            $item->update($callable($item->get()));
        }
        return $this;
    }

    /**
     * Resolve every result items regarding the path or path array provided.
     * @param string | string[] $paths Path or path array.
     * @return Result Chaining
     */
    public function grab($paths, $defaultValue = NULL)
    {
        foreach ($this->items as &$item) {

            if (!is_array($paths)) {
                $paths = [$paths];
            }

            $values = $this->grabEach($item, $paths, $defaultValue);

            $item = $values;
        }
        return $this;
    }

    /**
     * Resolve one result item regarding the path or path array provided.
     * @param ResolverItem $item Item to be resolved.
     * @param string | string[] $paths Path or path array.
     * @return ResolverItem[] Resolved items.
     */
    private function grabEach(ResolverItem $item, $paths, $defaultValue = NULL)
    {

        $resultValues = [];
        $handlers = [];
        foreach ($paths as $left => $right) {
            $handlerName = is_integer($left) ? $right : $left;
            $handlerValue = is_integer($left) ? NULL : $right;
            if (is_string($handlerName) && substr($handlerName, 0, 1) === Cnst::MODIFIER_CHAR) {
                $handlers[substr($handlerName, 1)] = $handlerValue;
            }
        }

        $resolver = new Resolver($item, isset($handlers['default-value']) ? $handlers['default-value'] : $defaultValue);

        $uniqueValues = [];

        foreach ($paths as $left => $right) {

            unset($pathObject);

            $path = is_integer($left) ? $right : $left;
            $pathArray = is_integer($left) ? NULL : $right;
            if ($path instanceof Path) {
                $pathObject = $path;
            } else {
                if (substr($path, 0, 1) !== Cnst::MODIFIER_CHAR) {
                    $pathObject = new Path($path);
                }

            }
            if (isset($pathObject)) {
                $key = $pathObject->getKey();

                // Resolve
                $result = $resolver->resolve($pathObject);

                // Recurse if need
                if ($pathArray !== NULL) {
                    $result->grab($pathArray);
                }

                $isUnique = TRUE;
                $value = $result->getRawValue();

                if ((isset($handlers['unique']) && $handlers['unique'])) {

                    // Unique modifier works on value returning an array because single value is by definition unique.
                    if (is_array($value)) {
                        $newValues = [];
                        foreach($value as $valueItem){

                            if (!in_array($valueItem->get(), $uniqueValues)) {
                                $uniqueValues[] = $valueItem->get();
                                $newValues[] = $valueItem;
                            }
                        }
                        $value = $newValues;
                    }
                }

                // Append value
                if(!(isset($handlers['unique']) && $handlers['unique']) || $isUnique === TRUE) {

                    if (isset($handlers['transform'])) {
                        $value->update($handlers['transform']($value->get(), $key));
                    }
                    if ($key !== NULL && substr($key, 0, 1) !== Cnst::PATH_INTERNAL_ID_CHAR) {
                        $resultValues[$key] = $value;
                    } else {
                        $resultValues[] = $value;
                    }
                }

            }
        }

        // return the very value instead of an array result contains just one single value,
        if (count($resultValues) === 1 && array_keys($resultValues)[0] === 0 && (!isset($handlers['keep-array']) || !$handlers['keep-array'])) {
            $resultValues = $resultValues[0];
        }
        return $resultValues;
    }

}
