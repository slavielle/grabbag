<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace slavielle\grabbag;

/**
 * Description of PathItem
 *
 * @author slavielle
 */
class PathItem {
  
  private $special;
  private $key;
  private $param;
  
  public function __construct($special, $key, $param){
    $this->special = $special;
    $this->key = $key;
    if(strlen($param)>0){
      $this->param = $param;
    }
  }
  
  public function getKey(){
    return $this->key;
  }
  
  public function hasParam(){
    return isset($this->param);
  }
  
  public function getParam(){
    return $this->param;
  }
  
  public function isKeyword(){
    return $this->special === '#';
  }
}
