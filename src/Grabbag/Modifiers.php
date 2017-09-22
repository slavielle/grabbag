<?php
/**
 * Created by PhpStorm.
 * User: slavielle
 * Date: 22/09/17
 * Time: 17:43
 */

namespace Grabbag;

/**
 * Class Modifiers
 *
 * Implement modifiers for path array.
 *
 * @package Grabbag
 */
class Modifiers
{
    private $modifiers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->modifiers = [];
    }

    /**
     * Submit a path array item. if path array item name's fitting as a modifier, modifier is recorded and the function
     * return TRUE to indicate modifier had been recognized and recorded. FALSE otherwise.
     * @param string $pathArrayItemName Path array item name.
     * @param mixed $pathArrayItemValue Path array item value.
     * @return bool Fit as a modifier.
     * @throws \Exception
     */
    public function submit($pathArrayItemName, $pathArrayItemValue)
    {
        if (is_string($pathArrayItemName) && preg_match('/^' . Cnst::REGEX_MODIFIER_CHAR . '(' . Cnst::REGEX_MODIFIER_NAME . ')(?:' . Cnst::REGEX_MODIFIER_PATH_ID_CHAR . '(' . Cnst::REGEX_PATH_INTERNAL_ID_CHAR . '?' . Cnst::REGEX_PATH_ID_NAME . '))?$/', $pathArrayItemName, $matches)) {
            if (!isset($this->modifiers[$matches[1]])) {
                $this->modifiers[$matches[1]] = [];
            }
            if (!empty($matches[2])) {
                if (!isset($this->modifiers[$matches[1]]['by_id'])) {
                    $this->modifiers[$matches[1]]['by_id'] = [];
                }
                if (!isset($this->modifiers[$matches[1]]['by_id'][$matches[2]])) {
                    $this->modifiers[$matches[1]]['by_id'][$matches[2]] = $pathArrayItemValue;
                }
                else {
                    throw new \Exception('modifier defined twice');
                }
            }
            else {
                if (!isset($this->modifiers[$matches[1]]['default'])) {
                    $this->modifiers[$matches[1]]['default'] = $pathArrayItemValue;
                }
                else {
                    throw new \Exception('modifier defined twice');
                }
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
    public function get($name, $pathId)
    {
        if (isset($this->modifiers[$name])) {
            if (isset($this->modifiers[$name]['by_id']) && isset($this->modifiers[$name]['by_id'][$pathId])) {
                return $this->modifiers[$name]['by_id'][$pathId];
            }
            else {
                return $this->modifiers[$name]['default'];
            }
        }
        return NULL;
    }

}