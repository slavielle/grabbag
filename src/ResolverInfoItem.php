<?php


namespace slavielle\grabbag;

use slavielle\grabbag\PathItem;

/**
 * Description of ResolverInfoItem
 *
 * @author slavielle
 */
class ResolverInfoItem {
  
  private $infoItem;
  
  public function __construct() {
    $this->infoItem = [];
  }
  
  public function append(ResolverInfoItem $infoItem){
    $this->infoItem[] = $infoItem;
  }
  
  public function setPathItemInfo(PathItem $pathItem){
    $this->infoItem['path_item'] = $pathItem;
  }
  
  public function setObjectInfo($object){
    $this->infoItem['object'] = $object;
  }
  
  public function setResolveTypeInfo($type){
    $this->infoItem['resolve-type'] = $type;
  }
  
}
