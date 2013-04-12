<?php

namespace Lily\Util;

class ApplicationContainer
{
    private $application;
    private $middleware;

    public function __construct($application, array $middleware = array())
    {
        $this->application = $application;
        $this->middleware = $middleware;
    }

    private function application()
    {
        return $this->application;
    }

    private function middleware()
    {
        return $this->middleware;
    }

    public function handler()
    {
        $handler = $this->application()->handler();
        $middleware = $this->middleware();

        return function ($request) use ($handler, $middleware) {
            foreach (array_reverse($middleware) as $_mw) {
                $handler = $_mw->wrapHandler($handler);
            }

            return $handler($request);
        };
    }
}
