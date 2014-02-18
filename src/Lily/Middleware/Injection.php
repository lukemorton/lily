<?php

namespace Lily\Middleware;

class Injection
{
    private $map;

    public function __construct($config)
    {
        $this->map = $config['inject'];
    }

    public function __invoke($handler)
    {
        $map = $this->map;

        return function ($request) use ($handler, $map) {
            $request = $map + $request;
            return $handler($request);
        };
    }
}
