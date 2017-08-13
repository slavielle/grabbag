<?php

namespace slavielle\grabbag;

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
}
