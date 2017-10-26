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

use Grabbag\exceptions\ModifierException;

/**
 * Class Modifiers
 *
 * Implement modifiers for path-array.
 *
 * @package Grabbag
 */
class Modifiers
{

    // Modifier special char constant.
    const MODIFIER_CHAR = '?';
    const MODIFIER_PATH_ID_CHAR = '@';

    // Regex part constant.
    const REGEX_MODIFIER_CHAR = '\\' . self::MODIFIER_CHAR;
    const REGEX_MODIFIER_PATH_ID_CHAR = self::MODIFIER_PATH_ID_CHAR;
    const REGEX_MODIFIER_NAME = '[0-9a-zA-Z_-]+';

    // Modifiers info
    const MODIFIERS_INFO_LIST = [
        'call' => [],
        'consider' => [],
        'debug' => [],
        'default-value' => [],
        'exception-enabled' => [],
        'keep-array' => [],
        'transform' => [],
        'unique' => []
    ];

    // Class properties.
    private $modifiers;

    /**
     * Constructor
     */
    public function __construct($pathArray)
    {
        $this->modifiers = [];

        foreach ($pathArray as $left => $right) {
            $handlerName = (int)$left === $left ? $right : $left;
            $handlerValue = (int)$left === $left ? TRUE : $right;
            $this->submit($handlerName, $handlerValue);
        }
    }

    /**
     * Submit a path-array item. if path-array item name's fitting as a modifier, modifier is recorded and the function
     * return TRUE to indicate modifier had been recognized and recorded. FALSE otherwise.
     * @param string $pathArrayItemName path-array item name.
     * @param mixed $pathArrayItemValue path-array item value.
     * @return bool Fit as a modifier.
     * @throws \Exception
     */
    private function submit($pathArrayItemName, $pathArrayItemValue)
    {
        if (is_string($pathArrayItemName) && preg_match('/^' . self::REGEX_MODIFIER_CHAR . '(' . self::REGEX_MODIFIER_NAME . ')(?:' . self::REGEX_MODIFIER_PATH_ID_CHAR . '(' . Path::REGEX_PATH_INTERNAL_ID_CHAR . '?' . Path::REGEX_PATH_ID_NAME . '))?$/', $pathArrayItemName, $matches)) {

            // Test if modifier exists.
            if (!array_key_exists($matches[1], self::MODIFIERS_INFO_LIST)) {
                throw new ModifierException(ModifierException::ERR_2, $matches[1]);
            }

            // Create modifier namespace in $modifiers property.
            if (!isset($this->modifiers[$matches[1]])) {
                $this->modifiers[$matches[1]] = [];
            }

            // Create by-id namespace in $modifiers property.
            if (!empty($matches[2])) {
                if (!isset($this->modifiers[$matches[1]]['by_id'])) {
                    $this->modifiers[$matches[1]]['by_id'] = [];
                }
                $this->modifiers[$matches[1]]['by_id'][$matches[2]] = $pathArrayItemValue;
            }

            // Create default namespace (for modifiers without id) in $modifiers property.
            else {
                $this->modifiers[$matches[1]]['default'] = $pathArrayItemValue;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Test if the modifier exists.
     * @param $name modifier name.
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->modifiers[$name]);
    }

    /**
     * Get the modifier default value.
     * @param string $name Modifier name.
     * @return mixed Default modifier value.
     */
    public function getDefault($name)
    {
        return $this->modifiers[$name]['default'];
    }

    /**
     * Get the modifier value depending on the path ID.
     * @param string $name Modifier name.
     * @return mixed Modifier value.
     */
    public function get($name, $pathId = NULL)
    {
        if (isset($this->modifiers[$name])) {
            if ($pathId !== NULL && isset($this->modifiers[$name]['by_id']) && isset($this->modifiers[$name]['by_id'][$pathId])) {
                return $this->modifiers[$name]['by_id'][$pathId];
            }
            else {
                return $this->modifiers[$name]['default'];
            }
        }
        return NULL;
    }

}