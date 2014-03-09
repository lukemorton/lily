<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Middleware;

use Lily\Middleware\Session\NativeStore;

class Session
{
    private $store;

    public function __construct($config = NULL)
    {
        if (isset($config['store'])) {
            $this->store = $config['store'];
        } else {
            $this->store = new NativeStore;
        }
    }

    public function __invoke($handler)
    {
        $store = $this->store;

        return function ($request) use ($handler, $store) {
            $request = $store->get($request);
            $response = $handler($request);
            $response = $store->set($request, $response);
            return $response;
        };
    }
}
