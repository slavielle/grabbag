<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace slavielle\grabbag;

use slavielle\grabbag\ResolverInfoItem;

/**
 * Description of ResolverInfo
 *
 * @author slavielle
 */
class ResolverInfo {
  
  private $info;
  
  public function __construct() {
    $this->info = [];
  }
  
  public function append(ResolverInfoItem $infoItem){
    $this->info[] = $infoItem;
  }
  
}
