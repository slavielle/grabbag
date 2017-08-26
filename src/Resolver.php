<?php

namespace slavielle\grabbag;

use slavielle\grabbag\Path;
use slavielle\grabbag\Result;
use slavielle\grabbag\exceptions\NotAdressableException;
use slavielle\grabbag\exceptions\PropertyNotFoundException;
use slavielle\grabbag\exceptions\UnknownPathKeywordException;
/**
 * Resolver allows to resolve path applied to an object in order to get a result.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 */
class Resolver {

  protected $object;

  public function __construct($object) {
    if(is_array($object)){
        $this->object = $object;
    }
    else {
        $this->object = [$object];
    }
    $this->pathArray = [];
  }

  public function resolve(Path $path) {
    if($path->isExceptionEnabled()){
        $objects = $this->resolveRecurse($path, $this->object);
    }
    else {
        try{
            $objects = $this->resolveRecurse($path, $this->object);
        } catch(NotAdressableException $e){
            return new Result([$path->getDefaultValue()]);
        } catch(PropertyNotFoundException $e){
            return new Result([$path->getDefaultValue()]);
        }
    }
    return new Result($objects);
  }
  
  /**
   * Resolve recursively for each PathItem instance in the $path.
   * @param Path $path
   * @param type $objects
   * @return type
   */
  private function resolveRecurse(Path $path, $objects){
    if((NULL !== ($pathItem = $path->next()))){
      $resultObjects = $this->resolveEach($pathItem, $objects);
      return $this->resolveRecurse($path, $resultObjects);
    }
    return $objects;
  }
  
  /**
   * Resolve for each object in objects regarding provided path item.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $objects
   * @return type
   * @throws NotAdressableException
   */
  private function resolveEach(PathItem $pathItem, $objects){
    $resultObjects = [];
    foreach($objects as $object){
      if($pathItem->isKeyword()){
        $resultObjects = array_merge($resultObjects,$this->resolveKeyword($pathItem, $object));
      }
      else if($pathItem->isSymbol()){
        $resultObjects[] = $this->resolveSymbol($pathItem, $object);
      }
      else {
        $resultObjects[] = $this->resolveType($pathItem, $object);
      }
    }
    return $resultObjects;
  }
  
  private function resolveSymbol(PathItem $pathItem, $object){
    switch($pathItem->getKey()){
      case '.':
        return $object;
      case '..':
        break;
    }
  }
  
  private function resolveType(PathItem $pathItem, $object){
    if (is_object($object)) {
      return $this->resolveObject($pathItem, $object);
    } else if (is_array($object)) {
      return $this->resolveArray($pathItem, $object);
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
   * @return type
   * @throws PropertyNotFoundException
   */
  private function resolveObject(PathItem $pathItem, $object) {
    
    // Test object property
    if (isset($object->{$pathItem->getKey()})) {
      return $this->resolveObjectProperty($pathItem, $object);
    }
    
    // Test if method exists with its key name.
    else if (method_exists($object, $pathItem->getKey())) {
      return $this->resolveObjectMethod($pathItem, $object);
    }
    
    // Test if method exists with "get" + its capitalized key name.
    else if (method_exists($object, 'get' . ucfirst($pathItem->getKey()))){
      return $this->resolveObjectMethod($pathItem, $object, TRUE);
    }
    
    else {
      throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on object', $pathItem->getKey()));
    }
  }

  
  /**
   * Resolve object property : Get the value form the object method defined in the $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @return type
   */
  private function resolveObjectProperty(PathItem $pathItem, $object) {
    return $object->{$pathItem->getKey()};
  }

  
  /**
   * Resolve object property : Get the value form the object property defined in the $pathItem.
   * @param \slavielle\grabbag\PathItem $pathItem
   * @param type $object
   * @param type $prefixWithGet
   * @return type
   */
  private function resolveObjectMethod(PathItem $pathItem, $object, $prefixWithGet = FALSE) {
    $params = [];
    if ($pathItem->hasParam()) {
      $params = $pathItem->getParams();
    }
    
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
   * @return type
   */
  private function resolveArray(PathItem $pathItem, $object) {

    if (isset($object[$pathItem->getKey()])){
        return $object[$pathItem->getKey()];
    }
    throw new PropertyNotFoundException(sprintf('Can\'t resolve "%s" on array', $pathItem->getKey()));
  }
 
}
