<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Path;
use slavielle\grabbag\Result;
use slavielle\grabbag\ResolverInfo;
use slavielle\grabbag\ResolverInfoItem;
use slavielle\grabbag\exceptions\NotAdressableException;
use slavielle\grabbag\exceptions\PropertyNotFoundException;
use slavielle\grabbag\exceptions\UnknownPathKeywordException;
use slavielle\grabbag\exceptions\PathParsingException;

/**
 * Description of ObjectWalker
 *
 * @author slavielle
 */
class Resolver {

  private $object;

  public function __construct($object) {
    if(is_array($object)){
        $this->object = $object;
    }
    else {
        $this->object = [$object];
    }
    $this->pathArray = [];
  }

  public function resolve(Path $path, $defaultValue = NULL, $enableException = FALSE) {
    $infos = new ResolverInfo();
    if($enableException){
        $objects = $this->resolveRecurse($path, $this->object, $infos);
    }
    else {
        try{
            $objects = $this->resolveRecurse($path, $this->object, $infos);
        } catch(NotAdressableException $e){
            return new Result([$defaultValue], $infos);
        } catch(PropertyNotFoundException $e){
            return new Result([$defaultValue], $infos);
        }
    }
    return new Result($objects, $infos);
  }
  
  /**
   * Resolve recursively for each PathItem instance in the $path.
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
  
  /**
   * Resolve for each object in objects regarding provided path item.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $objects
   * @param ResolverInfo $infos
   * @return type
   * @throws NotAdressableException
   */
  private function resolveEach(PathItem $pathItem, $objects, ResolverInfo $infos){
    $resultObjects = [];
    $info = new ResolverInfoItem();
    foreach($objects as $object){
      if($pathItem->isKeyword()){
        $resultObjects = array_merge($resultObjects,$this->resolveKeyword($pathItem, $object));
      }
      else {
        $infoEach = new ResolverInfoItem();
        $infoEach->setPathItemInfo($pathItem);
        $resultObjects[] = $this->resolveType($pathItem, $object, $infoEach);
        $info->append($infoEach);
      }
    }
    $infos->append($info);
    return $resultObjects;
  }
  
  private function resolveType(PathItem $pathItem, $object, ResolverInfoItem $info){
    if (is_object($object)) {
      return $this->resolveObject($pathItem, $object, $info);
    } else if (is_array($object)) {
      return $this->resolveArray($pathItem, $object, $info);
    } else {
      throw new NotAdressableException('Can\'t resolve');
    }
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
      case 'this':
        $resultObjects[] = $object;
        break;  
      default :
        throw new UnknownPathKeywordException(sprintf('Unknown keyword "#%s" in path', $pathItem->getKey()));
    }
    return $resultObjects;
  }
  
  
  /**
   * Resolve object regarding $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param ResolverInfoItem $info
   * @return type
   * @throws PropertyNotFoundException
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
      throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on object', $pathItem->getKey()));
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
      $params = $pathItem->getParams();
    }

    $info->setResolveTypeInfo('object-method');
    
    try{
        return call_user_method_array($prefixWithGet ? 'get' . ucfirst($pathItem->getKey()) : $pathItem->getKey(), $object, $params);
    }
    catch(\Exception $e){
        throw new NotAdressableException('Parameters passed to method throw an exception');
    }
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
    if (isset($object[$pathItem->getKey()])){
        return $object[$pathItem->getKey()];
    }
    throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on array', $pathItem->getKey()));
  }

}
