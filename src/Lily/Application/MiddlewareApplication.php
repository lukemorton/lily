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

    public function __construct($config = NULL)
    {
        if (isset($config['handler'])) {
            $this->handler = $config['handler'];
        }

        if (isset($config['middleware'])) {
            $this->middleware = $config['middleware'];
        }
    }

    protected function handler()
    {
        return $this->handler;
    }

    protected function middleware()
    {
        return $this->middleware;
    }

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
