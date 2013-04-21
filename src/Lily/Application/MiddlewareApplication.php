<?php

namespace Lily\Application;

class MiddlewareApplication
{
    private $application;
    private $middleware;

    public function __construct(array $pipeline)
    {
        $this->handler = array_shift($pipeline);
        $this->middleware = $pipeline;
    }

    private function handler()
    {
        return $this->handler;
    }

    private function middleware()
    {
        return $this->middleware;
    }

    public function __invoke($request)
    {
        $handler = $this->handler();

        foreach ($this->middleware() as $_mw) {
            $handler = $_mw($handler);
        }

        return $handler($request);
    }
}
