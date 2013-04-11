<?php

namespace Lily\Mock;

class Middleware
{
    private $wrapper;

    public function __construct($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function wrapHandler($handler)
    {
        return function ($request) use ($handler) {
            $wrapper = $this->wrapper;
            $wrapper();
            return $handler($request);
        };
    }
}
