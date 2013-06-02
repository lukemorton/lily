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
            $request = $store->get($request);
            $response = $handler($request);
            $response = $store->set($response);
            return $response;
        };
    }
}
