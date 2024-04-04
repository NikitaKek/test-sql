<?php

namespace FpDbTest;

use Exception;

class TemplateValidator
{
    public function __construct(
        private readonly string $regex
    ) {
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @param string $query
     * @param array $args
     * @return bool
     * @throws Exception
     */
    public function validate(string $query, array $args): bool
    {
        preg_match_all($this->getRegex(), $query, $matches, PREG_OFFSET_CAPTURE);
        $matches = $matches[0];

        if (count($matches) != count($args)) {
            throw new Exception($matches > $args ? 'too few args' : 'too many args');
        }

        $open = substr_count($query, '{');
        $close = substr_count($query, '}');
        if ($open !== $close) {
            throw new Exception('Error in implementation of conditional blocks');
        }

        return true;
    }
}
