<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    const REGEX = "/\?[dfa#]*/";

    private mysqli $mysqli;
    private Skip $skip;

    public function __construct(?mysqli $mysqli = null)
    {
        $this->skip = new Skip();
//        $this->mysqli = $mysqli;
    }

    /**
     * @param string $query
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function buildQuery(string $query, array $args = []): string
    {
        preg_match_all(self::REGEX, $query, $matches, PREG_OFFSET_CAPTURE);
        $matches = $matches[0];

        (new TemplateValidator(self::REGEX))->validate($query, $args);
        $patterns = PatternService::getPatterns($query, self::REGEX);

        $offset = 0;
        foreach ($args as $i => $arg) {
            $pattern = $patterns[$i];

            if ($arg instanceof Skip) {
                $arg->run($query);
                continue;
            }

            $arg = PatternService::formatArgument($arg, $patterns[$i]);

            $query = substr_replace($query, $arg, $pattern->getPosition() + $offset, $pattern->getLength());

            $offset += strlen($arg) - $pattern->getLength();
        }

        return self::removeBrackets($query);
    }

    private static function removeBrackets($query): string
    {
        $query = str_replace('{', '', $query);
        return str_replace('}', '', $query);
    }

    public function skip(): Skip
    {
        return $this->skip;
    }
}
