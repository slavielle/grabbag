<?php

namespace slavielle\grabbag;

use slavielle\grabbag\PathItem;

class Path {

  private $pathArray;
  private $index;
    
  public function __construct($path){
    while(1){
      $matches = [];
      $match_result = preg_match('/^(#)?([a-zA-Z_]+)(?:\(([^\)]+)\))?\.?(.*)$/', $path, $matches);
      if($match_result){
        $this->pathArray[] = new PathItem($matches[1], $matches[2], $matches[3]);
        $path = $matches[4];
        if(strlen($path) === 0){
          break;
        }
      }
      else{
        throw new \Exception('Can \t parse path');
      }
    }
    $this->rewind();
  }
  
  public function rewind(){
    if(count($this->pathArray) > 0){
      $this->index = 0;
    }
    else{
      $this->index = NULL;
    }
  }
  
  public function next(){
    if($this->index !== NULL){
      $val = $this->pathArray[$this->index];
      $this->index = $this->index <= count($this->pathArray) ? $this->index + 1 : NULL;
      return $val;
    }
    return NULL;
  }
  
}
