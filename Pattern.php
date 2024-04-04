<?php

namespace FpDbTest;

class Pattern
{
    private int $length;

    public function __construct(
        private readonly string $specifier,
        private readonly int    $position
    ) {
        $this->length = strlen($this->specifier);
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getSpecifier(): string
    {
        return $this->specifier;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
