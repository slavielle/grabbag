<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace slavielle\grabbag;

use slavielle\grabbag\exceptions\PathParsingException;

/**
 * Description of PathItem
 *
 * @author slavielle
 */
class PathItem {

    private $special;
    private $key;
    private $param;

    public function __construct($special, $key, $param) {
        $this->special = $special;
        $this->key = $key;
        if (strlen($param) > 0) {
            $this->param = $param;
        }
    }

    public function getKey() {
        return $this->key;
    }

    public function hasParam() {
        return isset($this->param);
    }

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

    public function isKeyword() {
        return $this->special === '#';
    }

}
