<?php

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
