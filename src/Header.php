<?php

namespace LeafyTech\Core;

class Header
{
    /**
     * @var array
     */
    protected array $headers = [];

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function __get(string $name)
    {
        return $this->headers[$name] ?? null;
    }
}