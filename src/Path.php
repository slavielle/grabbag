<?php

namespace slavielle\grabbag;

use slavielle\grabbag\PathItem;
use slavielle\grabbag\exceptions\PathParsingException;

/**
 * Path allows to define path to be grabbed.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package slavielle\grabbag
 */
class Path {

    private $pathArray;
    private $index;
    private $key;
    private $defaultValue;
    private $enableException;
    
   /**
    * Constructor.
    * @param string $path The path itself.
    * @param mixed $defaultValue The default value to be returned when a path does not apply.
    * @param Boolean $enableException Enable exception.
    * @throws PathParsingException 
    */
    public function __construct($path, $defaultValue = NULL, $enableException = FALSE) {

        $path = $this->parseKey($path);
        $this->defaultValue = $defaultValue;
        $this->enableException = $enableException;

        while (1) {
            $matches = [];
            $match_result = preg_match('/^(#)?([0-9a-zA-Z_]+|\.\.|\.)(?:\(([^\)]+)\))?\/?(.*)$/', $path, $matches);
            if ($match_result) {
                $this->pathArray[] = new PathItem($matches[1], $matches[2], $matches[3]);
                $path = $matches[4];
                if (strlen($path) === 0) {
                    break;
                }
            } else {
                throw new PathParsingException('Can \t parse path');
            }
        }
        $this->rewind();
    }
    
   /**
    * Parse the key part of a path.
    * 
    * In some case, path can have key part. The key part is located on start
    * of the path : 
    * 
    * "theKey:the/rest/of/my/path"
    * 
    * @param string $path Path string.
    * @return string Unconsumed path part.
    */
    public function parseKey($path) {
        $matches = [];
        $match_result = preg_match('/^([0-9a-zA-Z_]+:)(.*)$/', $path, $matches);
        if ($match_result) {
            $this->key = substr($matches[1], 0, -1);
            $path = $matches[2];
        }
        return $path;
    }
    
  /**
   * Rewind the path item pointer position.
   */
    public function rewind() {
        if (count($this->pathArray) > 0) {
            $this->index = 0;
        } else {
            $this->index = NULL;
        }
    }

  /**
   * Move the path item pointer to next position.
   * @return PathItem | NULL The next path item if any or NULL.
   */
    public function next() {
        if ($this->index !== NULL) {
            $val = $this->pathArray[$this->index];
            $this->index = $this->index + 1 < count($this->pathArray) ? $this->index + 1 : NULL;
            return $val;
        }
        return NULL;
    }
   
   /**
    * Get the path key.
    * @return string
    */
    public function getKey() {
        return $this->key;
    }

   /**
    * Indicate if exception is enabled.
    * @return type
    */
    public function isExceptionEnabled() {
        return $this->enableException;
    }

   /**
    * Get the default value.
    * @return type
    */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

}
