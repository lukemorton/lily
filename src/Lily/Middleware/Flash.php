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

class Flash
{
    public function __invoke($handler)
    {
        return function ($request) use ($handler) {
            if (isset($request['session']['_flash'])) {
                $request['flash'] = $request['session']['_flash'];
            }

            $response = $handler($request);

            if (isset($response['flash'])) {
                $response['session']['_flash'] = $response['flash'];
            } else if (isset($request['flash'])) {
                $response['session']['_flash'] = NULL;
            }

            return $response;
        };
    }
}
