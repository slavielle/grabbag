<?php

namespace Grabbag\tests\sourceData;

class Leaf1 {

    public $myName;
    public $myId;
    protected static $currentId = 0;

    public function __construct($name) {
        $this->myName = $name;
        $this->myId = 'ID#' . self::$currentId++;
    }

    public function getName() {
        return $this->myName;
    }

    public function getId() {
        return $this->myId;
    }

    public static function resetId() {
        self::$currentId = 0;
    }

}