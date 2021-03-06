<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Application;

/**
 * An application handler that wraps another handler in middleware handlers.
 */
class MiddlewareApplication
{
    private $handler;
    private $middleware = array();

    /**
     * Instantiate MiddlewareApplication optionally with configuration:
     *
     *  - `handler` an application handler function
     *  - `middleware` an array of middleware
     */
    public function __construct($config = NULL)
    {
        if (isset($config['handler'])) {
            $this->handler = $config['handler'];
        }

        if (isset($config['middleware'])) {
            $this->middleware = $config['middleware'];
        }
    }

    /**
     * Override in a sub class to define a handler statically.
     */
    protected function handler()
    {
        return $this->handler;
    }

    /**
     * Override in sub class to define an array of middleware statically.
     */
    protected function middleware()
    {
        return $this->middleware;
    }

    /**
     * Wraps middleware around the handler to create a new handler again
     * wrapping in middleware for each middleware provided until final handler
     * is invoked and response returned.
     */
    public function __invoke($request)
    {
        $middleware = $this->middleware();
        $handler = $this->handler();

        foreach ($middleware as $_mw) {
            $handler = $_mw($handler);
        }

        return $handler($request);
    }
}
