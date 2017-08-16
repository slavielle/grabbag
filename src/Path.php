<?php

namespace slavielle\grabbag;

use slavielle\grabbag\PathItem;
use slavielle\grabbag\exceptions\PathParsingException;

class Path {

  private $pathArray;
  private $index;
  private $key;
    
  public function __construct($path){
    
    $path = $this->parseKey($path);
              
    while(1){
      $matches = [];
      $match_result = preg_match('/^(#)?([0-9a-zA-Z_]+)(?:\(([^\)]+)\))?\.?(.*)$/', $path, $matches);
      if($match_result){
        $this->pathArray[] = new PathItem($matches[1], $matches[2], $matches[3]);
        $path = $matches[4];
        if(strlen($path) === 0){
          break;
        }
      }
      else{
        throw new PathParsingException('Can \t parse path');
      }
    }
    $this->rewind();
  }
  public function parseKey($path){
      $matches = [];
      $match_result = preg_match('/^([0-9a-zA-Z_]+:)(.*)$/', $path, $matches);
      if($match_result){
          $this->key = substr($matches[1], 0, -1);
          $path = $matches[2];
      }
      return $path;
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
      $this->index = $this->index + 1 < count($this->pathArray) ? $this->index + 1 : NULL;
      return $val;
    }
    return NULL;
  }
  
  public function getKey(){
      return $this->key;
  }
  
}
