<?php

namespace Grabbag;

use Grabbag\exceptions\PathParsingException;

/**
 * PathItem composing a Path.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class PathItem {

    private $special;
    private $key;
    private $param;
    
   /**
    * Constructor.
    * @param string $special Special character prefixing the key (e.g. '#' in '#each).
    * @param string $key Key (can be a method, à property name, an array key).
    * @param string $param (param when $key is a method with param).
    */
    public function __construct($special, $key, $param) {
        $this->special = $special;
        $this->key = $key;
        if (strlen($param) > 0) {
            $this->param = $param;
        }
    }

   /**
    * Key property getter.
    * @return string
    */
    public function getKey() {
        return $this->key;
    }

   /**
    * Test if param was defined of not.
    * @return bool
    */
    public function hasParam() {
        return isset($this->param);
    }

   /**
    * Param property getter.
    * @return mixed Parameter value.
    * @throws PathParsingException
    */
    public function getParams() {
        $matches = [];

        // String parameter.
        if (preg_match('/^"([^"]*)"$/', $this->param, $matches)) {
            return [$matches[1]];
        }

        // Numeric parameter expected.
        else {

            // Numeric parameter.
            if (is_numeric($this->param)) {
                return [$this->param + 0];
            }

            // Parse error
            else {
                throw new PathParsingException(sprintf('can\'t parse parameter ""'));
            }
        }
    }

   /**
    * Test if Key is a keyword prefixed with '#'.
    * @return bool
    */
    public function isKeyword() {
        return $this->special === '#';
    }
    
   /**
    * Test if key is a symbol
    * @return bool
    */
    public function isSymbol() {
        return !$this->isKeyword() && in_array($this->key, ['.', '..']);
    }
}