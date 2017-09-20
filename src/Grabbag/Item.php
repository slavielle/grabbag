<?php

namespace Grabbag;

use \Grabbag\exceptions\ResolveItemStackEmptyException;
/**
 * Class Item
 *
 * Implement resolver items used by Resolver to store item (value get from objects regarding the path) values and info.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */

class Item
{

    private $item;
    private $key;
    private $previous;

    /**
     * Item constructor.
     * @param mixed $item Item value.
     * @param string|integer Item key.
     */
    public function __construct($item, $key=NULL)
    {
        $this->update($item, $key);
        $this->previous = [];
    }

    /**
     * Getter for item property.
     * @return mixed Item value.
     */
    public function get()
    {
        return $this->item;
    }

    /**
     * Getter for key property.
     * @return string|integer $key value.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Push new item value on stack.
     * @param mixed $item Item value.
     * @param string|integer Item key.
     * @return mixed
     */
    public function push($item, $key = NULL)
    {
        $this->previous[] = [
            'item' => $this->item,
            'key' => $this->key
        ];
        $this->item = $item;
        $this->key = $key;
    }

    /**
     * Pop value from stack.
     * @return mixed Item value.
     * @throws \ResolveItemStackEmptyException
     */
    public function pop()
    {
        $popped = array_pop($this->previous);
        if ($popped === NULL) {
            throw new ResolveItemStackEmptyException(ResolveItemStackEmptyException::CODE_1);
        }
        $this->item = $popped['item'];
        $this->key = $popped['key'];

        return $this->item;
    }

    /**
     * Update top stack item without pushing it on stack.
     * @param mixed $item Item value.
     * @param string|integer Item key.
     * @param mixed $item
     */
    public function update($item, $key = NULL)
    {
        $this->item = $item;
        $this->key = $key;
    }

    /**
     * Prepare $item to normalize it to be an array of Item.
     * @param Item | mixed $item
     * @return array|Item
     */
    public static function prepareResolverItem($item)
    {
        if (is_array($item)) {
            return $item instanceof Item ? $item : [new Item($item)];
        } else {
            return $item instanceof Item ? [$item] : [new Item($item)];
        }
    }


}
