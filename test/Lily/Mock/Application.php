<?php

namespace Lily\Mock;

class Application
{
    private $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function __invoke($request)
    {
    	$handler = $this->handler;
        return $handler($request);
    }
}
