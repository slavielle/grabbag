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
 * Path allows to define path to be grabbed.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Path
{

    const PATH_INTERNAL_ID_CHAR = '~';
    const PATH_ID_SEPARATOR = ':';

    const REGEX_PATH_INTERNAL_ID_CHAR = self::PATH_INTERNAL_ID_CHAR;
    const REGEX_PATH_ID_SEPARATOR = self::PATH_ID_SEPARATOR;
    const REGEX_PATH_ID_NAME = '[0-9a-zA-Z_-]+';
    const REGEX_PATH_KEYWORD_PREFIX = PathItem::PATH_ITEM_KEYWORD_PREFIX;
    const REGEX_PATH_NUMERICAL_INDEX_PREFIX = PathItem::PATH_ITEM_NUMERICAL_INDEX_PREFIX;
    const REGEX_PATH_SPECIAL_CHAR = self::REGEX_PATH_KEYWORD_PREFIX . '|' . self::REGEX_PATH_NUMERICAL_INDEX_PREFIX;
    const REGEX_PATH_NAME = '[0-9a-zA-Z_]+|\.\.|\.';
    const REGEX_PATH_PARAMETER = '\(([^\)]+)\)';

    private $pathItemList;
    private $index;
    private $pathId;
    private $mutipleMatching;

    /**
     * Constructor.
     * @param string $path The path itself.
     * @throws PathException
     */
    public function __construct($path)
    {

        $path = $this->parsePathId($path);
        $this->mutipleMatching = FALSE;

        while (1) {
            $matches = [];
            $regex =
                '/^(' .
                Path::REGEX_PATH_SPECIAL_CHAR . ')?(' .
                Path::REGEX_PATH_NAME . ')(?:' .
                Path::REGEX_PATH_PARAMETER . ')?\/?(.*)$/';
            $match_result = preg_match($regex, $path, $matches);
            if ($match_result) {
                $pathItem = new PathItem($matches[1], $matches[2], $matches[3]);
                $this->mutipleMatching = $this->mutipleMatching || $pathItem->isMutipleMatching();
                $this->pathItemList[] = $pathItem;
                $path = $matches[4];
                if (strlen($path) === 0) {
                    break;
                }

            }
            else {
                throw new PathException(PathException::ERR_3, [$path]);
            }
        }
        $this->rewind();
    }

    /**
     * Rewind the path item pointer position.
     */
    public function rewind()
    {
        if (count($this->pathItemList) > 0) {
            $this->index = 0;
        }
        else {
            $this->index = NULL;
        }
    }

    /**
     * Move the path item pointer to next position.
     * @return PathItem The next path item if any or NULL.
     */
    public function next()
    {
        if ($this->index !== NULL) {
            $val = $this->pathItemList[$this->index];
            $this->index = $this->index + 1 < count($this->pathItemList) ? $this->index + 1 : NULL;
            return $val;
        }
        return NULL;
    }

    /**
     * Get the path id.
     * @return string
     */
    public function getPathId()
    {
        return $this->pathId;
    }

    /**
     * Getter for mutipleMatching property.
     *
     * mutipleMatching indicates whether path contains items leading to produce multiple results.
     *
     * @return bool
     */
    public function isMutipleMatching()
    {
        return $this->mutipleMatching;
    }

    /**
     * Parse the path id part of a path.
     *
     * In some case, path can have path id part. The path id part is located on start
     * of the path :
     *
     * "thePathId:the/rest/of/my/path"
     *
     * @param string $path Path string.
     * @return string Unconsumed path part.
     */
    private function parsePathId($path)
    {
        $matches = [];
        $regex = '/^' .
            '(' .
            Path::REGEX_PATH_INTERNAL_ID_CHAR . '?' .
            Path::REGEX_PATH_ID_NAME .
            Path::REGEX_PATH_ID_SEPARATOR .
            ')(.*)$/';
        $match_result = preg_match($regex, $path, $matches);
        if ($match_result) {
            $this->pathId = substr($matches[1], 0, -1);
            $path = $matches[2];
        }
        return $path;
    }

}
