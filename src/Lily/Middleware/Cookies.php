<?php

namespace Lily\Middleware;

class Cookies
{
    private $cookieDefaults = array();

    public function __construct(array $config = NULL)
    {
        if (isset($config['defaults'])) {
            $this->cookieDefaults = $config['defaults'];
        }
    }

    public function __invoke($handler)
    {
        $cookieDefaults = $this->cookieDefaults;

        return function ($request) use ($handler, $cookieDefaults) {
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
                        array('name' => $_name) + $_c + $cookieDefaults;
                }

                unset($response['cookies']);
            }

            return $response;
        };
    }
}
