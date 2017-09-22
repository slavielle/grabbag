<?php

namespace Grabbag;


class Cnst
{
    const MODIFIER_CHAR = '?';
    const MODIFIER_PATH_ID_CHAR = '@';
    const PATH_INTERNAL_ID_CHAR = '~';
    const PATH_ID_SEPARATOR = ':';
    const PATH_KEYWORD_PREFIX = '#';

    // Regex encapsulable const
    const REGEX_MODIFIER_CHAR = '\\' . self::MODIFIER_CHAR;
    const REGEX_MODIFIER_PATH_ID_CHAR = self::MODIFIER_PATH_ID_CHAR;
    const REGEX_MODIFIER_NAME = '[0-9a-zA-Z_-]+';
    const REGEX_PATH_INTERNAL_ID_CHAR = self::PATH_INTERNAL_ID_CHAR;
    const REGEX_PATH_ID_SEPARATOR = self::PATH_ID_SEPARATOR;
    const REGEX_PATH_ID_NAME = '[0-9a-zA-Z_-]+';
    const REGEX_PATH_KEYWORD_PREFIX = self::PATH_KEYWORD_PREFIX;
    const REGEX_PATH_NAME = '[0-9a-zA-Z_]+|\.\.|\.';
    const REGEX_PATH_PARAMETER = '\(([^\)]+)\)';
}