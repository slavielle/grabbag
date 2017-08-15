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
  
  public function resolveEach($path, $callable, $defaultValue = NULL, $enableException = FALSE){
    $resolver = new Resolver($this->value);
    $result = $callable($resolver->resolve(new Path($path), $defaultValue, $enableException));
    $this->value = $result->getValue();
    return $this;
  }
}
