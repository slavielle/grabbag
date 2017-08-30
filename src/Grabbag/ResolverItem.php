<?php

namespace Grabbag;

/**
 * Class ResolverItem
 *
 * Implement resolver items used by Resolver to store item (value get from objects regarding the path) values and info.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */

class ResolverItem {
    
    private $item;
    private $previous;

    /**
     * ResolverItem constructor.
     * @param mixed $item Item value.
     */
    public function __construct($item){
        $this->update($item);
        $this->previous = [];
    }

    /**
     * Getter for item property.
     * @return mixed
     */
    public function get(){
        return $this->item;
    }

    /**
     * Push new item value on stack.
     * @return mixed
     */
    public function push($item){
        $this->previous[] = $this->item;
        $this->item = $item;
    }

    /**
     * Pop value from stack.
     * @throws \Exception
     */
    public function pop(){
        $this->item = array_pop($this->previous);
        if($this->item === NULL){
            throw new \Exception('Can\'t pop an empty stack');
        }
    }

    /**
     * Update top stack item without pushing.
     * @param mixed $item
     */
    public function update($item){
        $this->item = $item;
    }
    
    
    
}
