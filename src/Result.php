<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Resolver;

class Result {

    private $value;
    private $infos;

    public function __construct($value, $infos) {
        $this->value = $value;
        $this->infos = $infos;
    }

    public function getValue($forceArray = false) {
        return count($this->value) === 1 && !$forceArray ? $this->value[0] : $this->value;
    }

    public function getInfos() {
        return $this->infos;
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
            $value = $result->getValue();
            if ($key === NULL) {
                $values[] = $value;
            } else {
                $values[$key] = $value;
            }
        }
        return $values;
    }

}
