<?php

namespace FpDbTest;

class Skip
{
    public function __construct()
    {
    }

    public function run(&$query): void
    {
        $open = strpos($query, '{');
        $close = strpos($query, '}');

        $query = substr_replace($query, '', $open, $close);
    }
}
