<?php

namespace Lily\Middleware;

class Flash
{
    public function __invoke($handler)
    {
        return function ($request) use ($handler) {
            if (isset($request['session']['_flash'])) {
                $request['flash'] = $request['session']['_flash'];
                unset($request['session']['_flash']);
            }

            $response = $handler($request);

            if (isset($response['flash'])) {
                $response['session']['_flash'] = $response['flash'];
            }

            return $response;
        };
    }
}
