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

use Grabbag\exceptions\PathException;

/**
 * PathItem composing a Path.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class PathItem
{
    const PATH_ITEM_KEYWORD_PREFIX = '%';
    const PATH_ITEM_NUMERICAL_INDEX_PREFIX = '#';
    const PATH_ITEM_KEYWORDS_METADATA = [
        'any' => [
            'mx' => TRUE,
        ],
        'key' => [
            'mx' => FALSE,
        ],
    ];
    const PATH_ITEM_SPECIAL_CHAR = [
        self::PATH_ITEM_NUMERICAL_INDEX_PREFIX => [

        ],
        self::PATH_ITEM_KEYWORD_PREFIX => [

        ]
    ];

    private $special;
    private $key;
    private $param;

    /**
     * Constructor.
     * @param string $special Special character prefixing the key (e.g. '%' in '%any).
     * @param string $key Key (can be a method, à property name, an array key).
     * @param string $param (param when $key is a method with param).
     * @throws PathException
     */
    public function __construct($special, $key, $param)
    {
        $this->checkAndSetSpecial($special);
        $this->key = $key;
        if (strlen($param) > 0) {
            $this->checkAndSetParam($param);
        }

        // Numeric key value shall be prefixed with numerical index prefix.
        if ((string)$key === (string)(int)$key && $special !== self::PATH_ITEM_NUMERICAL_INDEX_PREFIX) {
            throw new PathException(PathException::ERR_1);
        }

        // Check item
        $this->checkItem();
    }

    /**
     * Key property getter.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Test if param was defined of not.
     * @return bool
     */
    public function hasParam()
    {
        return isset($this->param);
    }

    /**
     * Param property getter.
     * @return mixed Parameter value.
     * @throws PathException
     */
    public function getParams()
    {
        return $this->param;
    }

    /**
     * Test if Key is a keyword prefixed with '%'.
     * @return bool
     */
    public function isKeyword()
    {
        return $this->special === self::PATH_ITEM_KEYWORD_PREFIX;
    }

    /**
     * Test if key is a symbol
     * @return bool
     */
    public function isSymbol()
    {
        return !$this->isKeyword() && in_array($this->key, ['.', '..']);
    }

    /**
     * Indicate if PathItem instance is a multi-matching one.
     *
     * Multi-matching means it can match several values when resolving.
     *
     * @return bool
     */
    public function isMutipleMatching()
    {

        if ($this->isKeyword() && self::GetKeywordMetadata($this->getKey())['mx'] === TRUE) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get metadata for a given keyword.
     * @param string $keyword Keyword.
     * @return array Metadata.
     * @throws PathException
     */
    public static function GetKeywordMetadata($keyword)
    {
        if (array_key_exists($keyword, self::PATH_ITEM_KEYWORDS_METADATA)) {
            return self::PATH_ITEM_KEYWORDS_METADATA[$keyword];
        }

        throw new PathException(PathException::ERR_5, [$keyword]);
    }

    /**
     * Check special char is valid and set "special" property.
     * @param string $special Special char.
     * @throws PathException
     */
    private function checkAndSetSpecial($special)
    {
        if ($special !== '' && !array_key_exists($special, self::PATH_ITEM_SPECIAL_CHAR)) {
            throw new PathException(PathException::ERR_6, [$special]);
        }
        $this->special = $special;
    }

    /**
     * Check param is valid and set "param" property.
     * @param string $param Parameter.
     * @throws PathException
     */
    private function checkAndSetParam($param)
    {
        $matches = [];

        // String parameter.
        if (preg_match('/^"([^"]*)"$/', $param, $matches)) {
            $this->param = [$matches[1]];
        }

        // Numeric parameter expected.
        else {

            // Numeric parameter.
            if (is_numeric($param)) {
                $this->param = [$param + 0];
            }

            // Parse error
            else {
                throw new PathException(PathException::ERR_2, [$param]);
            }
        }
    }

    private function checkItem()
    {
        if ($this->isKeyword() && isset($this->param)) {
            throw new PathException(PathException::ERR_7, [$this->key, $this->param[0]]);
        }

        if ($this->isSymbol() && isset($this->param)) {
            throw new PathException(PathException::ERR_8, [$this->key, $this->param[0]]);
        }
    }
}
