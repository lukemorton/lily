<?php

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
