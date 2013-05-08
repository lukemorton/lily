<?php

namespace Lily\Middleware;

class Cookies
{
    private $cookie_defaults = array();

    public function __construct(array $config = NULL)
    {
        if (isset($config['defaults'])) {
            $this->cookie_defaults = $config['defaults'];
        }
    }

    public function __invoke($handler)
    {
        $cookie_defaults = $this->cookie_defaults;

        return function ($request) use ($handler, $cookie_defaults) {
            $request['cookies'] = $request['headers']['cookies'];
            $response = $handler($request);

            if (isset($response['cookies'])) {
                if ( ! isset($response['headers']['Set-Cookie'])) {
                    $response['headers']['Set-Cookie'] = array();
                }

                foreach ($response['cookies'] as $_name => $_c) {
                    if ( ! is_array($_c)) {
                        $_c = array('value' => $_c);
                    }

                    $response['headers']['Set-Cookie'][] =
                        array('name' => $_name) + $_c + $cookie_defaults;
                }

                unset($response['cookies']);
            }

            return $response;
        };
    }
}
