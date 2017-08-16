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
  
  public function getValue($forceArray = false){
    return count($this->value) === 1 && !$forceArray ? $this->value[0] : $this->value;
  }
  
  public function getInfos(){
    return $this->infos;
  }
  
  public function each($callable){
    foreach($this->value as $item){
      $callable($item);
    }
  }
  
  public function transformEach($callable){
    foreach($this->value as &$item){
      $item = $callable($item);
    }
    return $this;
  }
  
  public function grab($paths, $defaultValue = NULL, $enableException = FALSE){
    foreach($this->value as &$item){
        $resolver = new Resolver($item);
        if(!is_array($paths)){
            $paths = [$paths];
        }
        $values = [];
        foreach($paths as $left=>$right){
            $path = is_integer($left) ? $right : $left;
            $pathArray = is_integer($left) ? NULL : $right;
            $pathObject = new Path($path);
            $key = $pathObject->getKey();
            $result = $resolver->resolve($pathObject, $defaultValue, $enableException);
            if($pathArray !== NULL){
                $result->grab($pathArray);
            }
            $value = $result->getValue();
            if($key === NULL){
                $values[] = $value;
            }
            else {
                $values[$key] = $value;
            }
            
        }
        if(count($values) === 1 && array_keys($values)[0] === 0){
            $values = $values[0];
        }
        $item = $values;
    }
    return $this;
  }
}
