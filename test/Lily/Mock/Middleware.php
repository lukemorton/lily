<?php

namespace Lily\Mock;

class Middleware
{
    private $wrapper;

    public function __construct($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function __invoke($handler)
    {
        $wrapper = $this->wrapper;

        return function ($request) use ($wrapper, $handler) {
            $wrapper();
            return $handler($request);
        };
    }
}
