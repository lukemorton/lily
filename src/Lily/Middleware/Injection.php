<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
