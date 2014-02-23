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

class DefaultHeaders
{
    private $headers;

    public function __construct($config)
    {
        $this->headers = $config['headers'];
    }

    public function __invoke($handler)
    {
        $headers = $this->headers;

        return function ($request) use ($handler, $headers) {
            $response = $handler($request);

            foreach ($headers as $_header => $_v) {
                if ( ! isset($response['headers'][$_header])) {
                    $response['headers'][$_header] = $_v;
                }
            }

            return $response;
        };
    }
}
