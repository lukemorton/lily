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

class ResponseStatusHandler
{
    private $statusHandlers;

    public function __construct($config)
    {
        $this->statusHandlers = $config['handlers'];
    }

    public function __invoke($handler)
    {
        $statusHandlers = $this->statusHandlers;

        return function ($request) use ($handler, $statusHandlers) {
            $response = $handler($request);

            if (isset($statusHandlers[$response['status']])) {
                $statusHandler = $statusHandlers[$response['status']];
                $request['original-response'] = $response;
                $response = $statusHandler($request);
            }

            return $response;
        };
    }
}
