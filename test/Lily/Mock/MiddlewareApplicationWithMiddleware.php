<?php

namespace Lily\Mock;

use Lily\Application\MiddlewareApplication;

class MiddlewareApplicationWithMiddleware extends MiddlewareApplication
{
    public $m;

    protected function middleware()
    {
        return array(
            function ($request) {
                return $request['middleware-message'];
            },
            
            function ($handler) {
                return function ($request) use ($handler) {
                    $request['middleware-message'] = $this->m;
                    return $handler($request);
                };
            },
        );
    }
}
