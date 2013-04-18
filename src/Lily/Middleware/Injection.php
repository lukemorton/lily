<?php

namespace Lily\Middleware;

class Injection
{
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function wrapHandler($handler)
    {
        $map = $this->map;

        return function ($request) use ($handler, $map) {
            $request = $map + $request;
            return $handler($request);
        };
    }
}
