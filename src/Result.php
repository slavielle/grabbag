<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;
use slavielle\grabbag\ResolverItem;

/**
 * Result implements resolve process result.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class Result {

    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue($forceArray = false) {
        
        $resultValue = $this->getValueRecurse($this->value);
        return count($resultValue) === 1 && !$forceArray ? $resultValue[0] : $resultValue;
    }
    
    private function getRawValue($forceArray = false) {
        return count($this->value) === 1 && !$forceArray ? $this->value[0] : $this->value;
    }
    
    private function getValueRecurse($array){
        $resultArray = [];
        foreach($array as $key => $arrayItem){
            if(is_array($arrayItem)){
                $resultArray[$key] = $this->getValueRecurse($arrayItem);
            }
            else if ($arrayItem instanceof ResolverItem){
                $resultArray[$key] = $arrayItem->get();
            }
            else {
                throw new \Exception('Unexpected type');
            }
        }
        return $resultArray;
    }
    
    public function each($callable) {
        foreach ($this->value as $item) {
            $callable($item);
        }
    }

    public function transformEach($callable) {
        foreach ($this->value as &$item) {
            $item = $callable($item);
        }
        return $this;
    }

    public function grab($paths) {
        foreach ($this->value as &$item) {

            if (!is_array($paths)) {
                $paths = [$paths];
            }

            $values = $this->grabEach($item, $paths);
            if (count($values) === 1 && array_keys($values)[0] === 0) {
                $values = $values[0];
            }

            $item = $values;
        }
        return $this;
    }

    private function grabEach($item, $paths) {
        $resolver = new Resolver($item);
        $values = [];
        foreach ($paths as $left => $right) {
            
            $path = is_integer($left) ? $right : $left;
            $pathArray = is_integer($left) ? NULL : $right;
            if($path instanceof Path){
                $pathObject = $path;
            }
            else{
                $pathObject = new Path($path);
            }
            $key = $pathObject->getKey();
            
            // Resolve
            $result = $resolver->resolve($pathObject);
            
            // Recurse if need
            if ($pathArray !== NULL) {
                $result->grab($pathArray);
            }
            
            // Append value
            $value = $result->getRawValue();
            if ($key === NULL) {
                $values[] = $value;
            } else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

}
