<?php

class List1 extends Leaf1 {

    public $objects;

    public function __construct($name) {
        parent::__construct($name);
        $this->objects = [];
    }

    public function appendObject($object, $key = NULL) {
        if ($key === NULL) {
            $this->objects[] = $object;
        } else {
            $this->objects[$key] = $object;
        }
    }

    public function getAllObjects() {
        return $this->objects;
    }

    public function getOneObject($indexOrName) {
        return $this->objects[$indexOrName];
    }

}