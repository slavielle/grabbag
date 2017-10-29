<?php

/*
 * This file is part of the Grabbag package.
 *
 * (c) Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grabbag;

use Grabbag\exceptions\ItemException;

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

    private $value;
    private $key;
    private $previous;

    /**
     * Item constructor.
     * @param mixed $item Item value.
     * @param string|integer Item key.
     */
    public function __construct($item, $key = NULL)
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
        return $this->value;
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
     * @param mixed $value Item value.
     * @param string|integer Item key.
     * @return mixed
     */
    public function push($value, $key = NULL)
    {
        $this->previous[] = [
            'value' => $this->value,
            'key' => $this->key
        ];
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * Pop value from stack.
     * @return mixed Item value.
     * @throws ItemException
     */
    public function pop()
    {
        $popped = array_pop($this->previous);
        if ($popped === NULL) {
            throw new ItemException(ItemException::ERR_1);
        }
        $this->value = $popped['value'];
        $this->key = $popped['key'];

        return $this->value;
    }

    /**
     * Update top stack item without pushing it on stack.
     * @param mixed $value Item value.
     * @param string|integer Item key.
     * @param mixed $value
     */
    public function update($value, $key = NULL)
    {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * Normalize $item to be an array of Item.
     * @param Item | mixed $item Item to normalize.
     * @return array|Item Normalized item.
     */
    public static function normalizeResolverItem($item)
    {
        return $item instanceof Item ? [$item] : [new Item($item)];
    }


}
