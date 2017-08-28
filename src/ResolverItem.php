<?php

namespace slavielle\grabbag;

class ResolverItem {
    
    private $item;
    private $previous;
    
    public function __construct($item){
        $this->update($item);
        $this->previous = [];
    }
    
    public function get(){
        return $this->item;
    }
    
    public function push($item){
        $this->previous[] = $this->item;
        $this->item = $item;
    }
    
    public function pop(){
        $this->item = array_pop($this->previous);
        if($this->item === NULL){
            throw new \Exception('Can\'t pop an empty stack');
        }
    }
    
    public function update($item){
        $this->item = $item;
    }
    
    
    
}
