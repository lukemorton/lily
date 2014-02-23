<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Application;

class MiddlewareApplication
{
    private $middleware;

    public function __construct($pipeline = NULL)
    {
        if ($pipeline !== NULL) {
            $this->middleware = $pipeline;
        }
    }

    protected function middleware()
    {
        return $this->middleware;
    }

    public function __invoke($request)
    {
        $middleware = $this->middleware();
        $handler = array_shift($middleware);

        foreach ($middleware as $_mw) {
            $handler = $_mw($handler);
        }

        return $handler($request);
    }
}
