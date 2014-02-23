<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Mock;

use Lily\Application\MiddlewareApplication;

class MiddlewareApplicationWithMiddleware extends MiddlewareApplication
{
    public $m;

    protected function middleware()
    {
        $m = $this->m;

        return array(
            function ($request) {
                return $request['middleware-message'];
            },
            
            function ($handler) use ($m) {
                return function ($request) use ($handler, $m) {
                    $request['middleware-message'] = $m;
                    return $handler($request);
                };
            },
        );
    }
}
