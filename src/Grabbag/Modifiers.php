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
        'call' => [
            'param_type' => 'callback',
            'empty_param_allowed' => FALSE,
            'targettable' => TRUE,
        ],
        'consider' => [
            'param_type' => 'callback',
            'empty_param_allowed' => FALSE,
            'targettable' => TRUE,
        ],
        'debug' => [
            'param_type' => 'callback',
            'empty_param_allowed' => TRUE,
            'targettable' => TRUE,
        ],
        'default-value' => [
            'param_type' => 'mixed',
            'empty_param_allowed' => FALSE,
            'targettable' => TRUE,
        ],
        'exception-enabled' => [
            'param_type' => 'bool',
            'empty_param_allowed' => TRUE,
            'targettable' => FALSE,
        ],
        'keep-array' => [
            'param_type' => 'bool',
            'empty_param_allowed' => TRUE,
            'targettable' => FALSE,
        ],
        'transform' => [
            'param_type' => 'callback',
            'empty_param_allowed' => FALSE,
            'targettable' => TRUE,
        ],
        'unique' => [
            'param_type' => 'bool',
            'empty_param_allowed' => TRUE,
            'targettable' => FALSE,
        ]
    ];

    // Class properties.
    private $modifiers;
    private $unmatchedPath;

    /**
     * Constructor.
     * @param string[] $pathArray Path array to get the modifiers from.
     */
    public function __construct($pathArray)
    {
        $this->modifiers = [];
        $this->unmatchedPath = [];

        foreach ($pathArray as $left => $right) {
            $handlerName = (int)$left === $left ? $right : $left;
            $handlerValue = (int)$left === $left ? TRUE : $right;
            if (!$this->submit($handlerName, $handlerValue)) {
                $this->unmatchedPath[$left] = $right;
            }
        }
    }

    /**
     * Test if the modifier exists.
     * @param string $name Modifier name.
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
     * @throws ModifierException.
     */
    public function getDefault($name)
    {
        if (isset($this->modifiers[$name])) {
            return $this->modifiers[$name]['default'];
        }
        throw new ModifierException(ModifierException::ERR_4, [$name]);
    }

    /**
     * Get the modifier value depending on the path ID.
     * @param string $name Modifier name.
     * @param string $pathId Target path id for the modifier.
     * @return mixed Modifier parameter value.
     * @throws ModifierException
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
        throw new ModifierException(ModifierException::ERR_4, [$name]);
    }


    /**
     * Get unmatched paths (path that are not modifiers).
     * @return array
     */
    public function getUnmatchedPath()
    {
        return $this->unmatchedPath;
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
                throw new ModifierException(ModifierException::ERR_2, [$matches[1]]);
            }

            // Check modifier parameter type
            self::checkModifierParamType($matches[1], $pathArrayItemValue);

            // Create modifier namespace in $modifiers property.
            if (!isset($this->modifiers[$matches[1]])) {
                $this->modifiers[$matches[1]] = [];
            }

            // Create by-id namespace in $modifiers property.
            if (!empty($matches[2])) {

                // Test if modifier is targettable
                if (self::MODIFIERS_INFO_LIST[$matches[1]]['targettable'] !== TRUE) {
                    throw new ModifierException(ModifierException::ERR_5, [$matches[1]]);
                }

                // Create by_id namespace in modifier property if not already set.
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
     * Helper function to check modifier parameter regarding modifier name.
     * It throws an exception if checking fails.
     * @param string $modifier_name Modifier name.
     * @param mixed $parameter Parameter to check.
     * @throws ModifierException
     */
    private static function checkModifierParamType($modifier_name, $parameter)
    {
        switch (self::MODIFIERS_INFO_LIST[$modifier_name]['param_type']) {
            case 'callback':
                if (!is_object($parameter) || !get_class($parameter) === 'Closure') {
                    throw new ModifierException(ModifierException::ERR_3, [$modifier_name, "Closure"]);
                }
                break;
            case 'bool':
                if (!is_bool($parameter)) {
                    throw new ModifierException(ModifierException::ERR_3, [$modifier_name, "boolean value"]);
                }
                break;
        }
    }

}