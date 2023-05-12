<?php

namespace LeafyTech\Core\Helpers;

class Env
{
    public static function get($key, $default = null)
    {
        if(isset($_ENV[$key])) return $_ENV[$key];
        return $default;
    }
}