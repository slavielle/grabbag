<?php

namespace Grabbag;

use Grabbag\PathItem;
use Grabbag\exceptions\PathParsingException;
use Grabbag\Cnst;

/**
 * Path allows to define path to be grabbed.
 *
 * @author Sylvain Lavielle <sylvain.lavielle@netelios.fr>
 * @package Grabbag
 */
class Path
{

    private $pathItemList;
    private $index;
    private $key;

    /**
     * Constructor.
     * @param string $path The path itself.
     * @throws PathParsingException
     */
    public function __construct($path)
    {

        $path = $this->parseKey($path);

        while (1) {
            $matches = [];
            $regex =
                '/^(' .
                Cnst::REGEX_PATH_KEYWORD_PREFIX . ')?(' .
                Cnst::REGEX_PATH_NAME . ')(?:' .
                Cnst::REGEX_PATH_PARAMETER . ')?\/?(.*)$/';
            $match_result = preg_match($regex, $path, $matches);
            if ($match_result) {
                $this->pathItemList[] = new PathItem($matches[1], $matches[2], $matches[3]);
                $path = $matches[4];
                if (strlen($path) === 0) {
                    break;
                }
            } else {
                throw new PathParsingException('Can \'t parse path');
            }
        }
        $this->rewind();
    }

    /**
     * Parse the key part of a path.
     *
     * In some case, path can have key part. The key part is located on start
     * of the path :
     *
     * "theKey:the/rest/of/my/path"
     *
     * @param string $path Path string.
     * @return string Unconsumed path part.
     */
    public function parseKey($path)
    {
        $matches = [];
        $regex = '/^' .
            '(' .
            Cnst::REGEX_PATH_INTERNAL_ID_CHAR . '?' .
            Cnst::REGEX_PATH_ID_NAME .
            Cnst::REGEX_PATH_ID_SEPARATOR .
            ')(.*)$/';
        $match_result = preg_match($regex, $path, $matches);
        if ($match_result) {
            $this->key = substr($matches[1], 0, -1);
            $path = $matches[2];
        }
        return $path;
    }

    /**
     * Rewind the path item pointer position.
     */
    public function rewind()
    {
        if (count($this->pathItemList) > 0) {
            $this->index = 0;
        } else {
            $this->index = NULL;
        }
    }

    /**
     * Move the path item pointer to next position.
     * @return PathItem | NULL The next path item if any or NULL.
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
     * Get the path key.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}
