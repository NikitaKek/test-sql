<?php

namespace FpDbTest;

use Exception;

class PatternService
{
    const SPECIFIER_STRING = "?";
    const SPECIFIER_IDENTIFIER = "?#";
    const SPECIFIER_NUMBER = "?d";
    const SPECIFIER_ARRAY = "?a";

    const NULL = 'NULL';

    /**
     * @param string $query
     * @param string $regex
     * @return Pattern[]
     */
    public static function getPatterns(string $query, string $regex): iterable
    {
        preg_match_all($regex, $query, $matches, PREG_OFFSET_CAPTURE);
        $matches = $matches[0];
        $patterns = [];
        foreach ($matches as $match) {
            $patterns[] = new Pattern($match[0], $match[1]);
        }

        return $patterns;
    }

    /**
     * @param mixed $arg
     * @param Pattern $pattern
     * @return string
     * @throws Exception
     */
    public static function formatArgument(mixed $arg, Pattern $pattern): string
    {
        return match ($pattern->getSpecifier()) {
            PatternService::SPECIFIER_STRING => PatternService::resolveString($arg),
            PatternService::SPECIFIER_IDENTIFIER => PatternService::resolveIdentifier($arg),
            PatternService::SPECIFIER_NUMBER => PatternService::resolveNumber($arg),
            PatternService::SPECIFIER_ARRAY => PatternService::resolveArray($arg),
            default => throw new Exception('Unknown specifier')
        };
    }

    private static function resolveString(mixed $arg): string
    {
        if (is_null($arg)) {
            return self::NULL;
        }

        return "'$arg'";
    }

    /**
     * @throws Exception
     */
    private static function resolveIdentifier(mixed $arg): string
    {
        if (is_null($arg)) {
            throw new Exception('Identifier cannot be null');
        }

        if (is_array($arg)) {
            return self::resolveIdentifierArray($arg);
        }

        return "`$arg`";
    }

    /**
     * @throws Exception
     */
    private static function resolveIdentifierArray(array $args): string
    {
        $array = [];

        foreach ($args as $arg) {
            $array[] = self::resolveIdentifier($arg);
        }

        return implode(', ', $array);
    }

    private static function resolveNumber(mixed $arg): string
    {
        if (is_null($arg)) {
            return self::NULL;
        }

        $arg = (int)$arg;

        return "$arg";
    }

    /**
     * @throws Exception
     */
    private static function resolveArray(mixed $arg): string
    {
        if (empty($arg)) {
            throw new Exception('Empty array');
        }

        if (!is_array($arg)) {
            throw new Exception('Argument must be array');
        }

        if (self::arrayIsList($arg)) {
            return self::resolveList($arg);
        } else {
            return self::resolveAssociative($arg);
        }
    }

    private static function resolveList(array $args): string
    {
        $array = [];

        if (self::isArrayOfNumbers($args)) {
            foreach ($args as $arg) {
                $array[] = self::resolveNumber($arg);
            }
        } else {
            foreach ($args as $arg) {
                $array[] = self::resolveString($arg);
            }
        }
        return implode(', ', $array);
    }

    private static function resolveAssociative(array $args): string
    {
        $array = [];

        if (self::isArrayOfNumbers($args)) {
            foreach ($args as $key => $arg) {
                $str = "`$key` = ";
                $str .= self::resolveNumber($arg);
                $array[] = $str;
            }
        } else {
            foreach ($args as $key => $arg) {
                $str = "`$key` = ";
                $str .= self::resolveString($arg);
                $array[] = $str;
            }
        }
        return implode(', ', $array);
    }

    private static function isArrayOfNumbers(array $args): bool
    {
        foreach ($args as $arg) {
            if (is_null($arg)) {
                continue;
            }
            if (is_string($arg)) {
                return false;
            }
        }
        return true;
    }

    private static function arrayIsList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
