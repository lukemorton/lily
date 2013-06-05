<?php

namespace Lily\Middleware\Session;

class CookieStore
{
    private $name = '_session';
    private $cookie;

    public function __construct(array $config)
    {
        if (isset($config['cookie'])) {
            if (isset($config['cookie']['name'])) {
                $this->name = $config['cookie']['name'];
                unset($config['cookie']['name']);
            }

            $this->cookie = $config['cookie'];
        }
    }

    public function get(array $request)
    {
        if (isset($request['cookies'][$this->name])) {
            $request['session'] =
                json_decode(
                    $request['cookies'][$this->name],
                    TRUE);
        }

        return $request;
    }

    public function set(array $response)
    {
        if (isset($response['session'])) {
            $response['cookies'][$this->name] =
                array('value' => json_encode($response['session']))
                + $this->cookie;
        }

        return $response;
    }
}
