<?php

namespace Lily\Middleware;

use Lily\Middleware\Session\NativeStore;

class Session
{
    private $store;

    public function __construct(array $config = NULL)
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
            $request['session'] = $store->get();

            $response = $handler($request);

            if (isset($response['session'])) {
                $store->set($response['session']);
            }

            return $response;
        };
    }
}
