<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Path;
use slavielle\grabbag\Result;
use slavielle\grabbag\ResolverInfo;
use slavielle\grabbag\ResolverInfoItem;

/**
 * Description of ObjectWalker
 *
 * @author slavielle
 */
class Resolver {

  private $object;

  public function __construct($object) {
    $this->object = $object;
    $this->pathArray = [];
  }

  public function resolve(Path $path) {
    $infos = new ResolverInfo();
    $objects = $this->resolveRecurse($path, [$this->object], $infos);
    return new Result($objects, $infos);
  }
  
  /**
   * Resolve recursively for eack PathItem instance in the $path.
   * @param Path $path
   * @param type $objects
   * @param ResolverInfo $infos
   * @return type
   */
  private function resolveRecurse(Path $path, $objects, ResolverInfo $infos){
    if((NULL !== ($pathItem = $path->next()))){
      $resultObjects = $this->resolveEach($pathItem, $objects, $infos);
      return $this->resolveRecurse($path, $resultObjects, $infos);
    }
    return $objects;
  }
  
  
  private function resolveEach(PathItem $pathItem, $objects, ResolverInfo $infos){
    $resultObjects = [];
    $info = new ResolverInfoItem();
    foreach($objects as $object){
      if($pathItem->isKeyword()){
        $resultObjects += $this->resolveKeyword($pathItem, $object);
      }
      else {
        $infoEach = new ResolverInfoItem();
        $infoEach->setPathItemInfo($pathItem);
        if (is_object($object)) {
          $resultObjects[] = $this->resolveObject($pathItem, $object, $infoEach);
        } else if (is_array($object)) {
          $resultObjects[] = $this->resolveArray($pathItem, $object, $infoEach);
        } else {
          throw new \Exception('Can\'t resolve');
        }
        $info->append($infoEach);
      }
    }
    $infos->append($info);
    return $resultObjects;
  }
  
  
  /**
   * Resolve keyword : Get value from object depending on the keyword specified in $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @return type
   */
  private function resolveKeyword(PathItem $pathItem, $object){
    $resultObjects = [];
    switch($pathItem->getKey()){
      case 'each':
        foreach($object as $item){
          $resultObjects[] = $item;
        }
        break;
    }
    return $resultObjects;
  }
  
  
  /**
   * Resolve object regarding $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param ResolverInfoItem $info
   * @return type
   * @throws \Exception
   */
  private function resolveObject(PathItem $pathItem, $object, ResolverInfoItem $info) {
    $info->setObjectInfo($object);
    
    // Test object property
    if (isset($object->{$pathItem->getKey()})) {
      return $this->resolveObjectProperty($pathItem, $object, $info);
    }
    
    // Test if method exists with its key name.
    else if (method_exists($object, $pathItem->getKey())) {
      return $this->resolveObjectMethod($pathItem, $object, $info);
    }
    
    // Test if method exists with "get" + its capitalized key name.
    else if (method_exists($object, 'get' . ucfirst($pathItem->getKey()))){
      return $this->resolveObjectMethod($pathItem, $object, $info, TRUE);
    }
    
    else {
      throw new \Exception(sprintf('Can\'t resolve "%s"', $pathItem->getKey()));
    }
  }

  
  /**
   * Resolve object property : Get the value form the object method defined in the $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param ResolverInfoItem $info
   * @return type
   */
  private function resolveObjectProperty(PathItem $pathItem, $object, ResolverInfoItem $info) {
    $info->setResolveTypeInfo('object-property');
    return $object->{$pathItem->getKey()};
  }

  
  /**
   * Resolve object property : Get the value form the object property defined in the $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param ResolverInfoItem $info
   * @param type $prefixWithGet
   * @return type
   */
  private function resolveObjectMethod(PathItem $pathItem, $object, ResolverInfoItem $info, $prefixWithGet = FALSE) {
    $params = [];
    if ($pathItem->hasParam()) {
      $matches = [];
      if (preg_match('/^"([^"]*)"$/', $pathItem->getParam(), $matches)) {
        $params[] = $matches[1];
      } else {
        $params[] = $pathItem->getParam();
      }
    }

    $info->setResolveTypeInfo('object-method');
    
    return call_user_method_array($prefixWithGet ? 'get' . ucfirst($pathItem->getKey()) : $pathItem->getKey(), $object, $params);
  }

  
  /**
   * Resolve array : Get the value form the array key defined in the $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param type $info
   * @return type
   */
  private function resolveArray(PathItem $pathItem, $object, &$info) {

    $info->setResolveTypeInfo('array-key');
    return $object[$pathItem->getKey()];
  }

}
