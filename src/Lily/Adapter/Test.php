<?php

namespace Lily\Adapter;

class Test
{
    private $dummyRequest = array(
        'host' => 'localhost',
        'port' => 80,
        'addr' => '127.0.0.1',
        'remote-addr' => '127.0.0.1',
        'scheme' => 'http',
        'method' => 'GET',
        'uri' => '/',
        'type' => 'text/html',
        'length' => 0,
        'headers' => array(
            'content-type' => 'text/html',
            'content-length' => 0,
            'user-agent' => 'Lily\Adapter\Test',
            'x-foo-bar' => 'foobar',
        ),
    );

    private $followRedirect;

    public function __construct($config = NULL)
    {
        if (isset($config['followRedirect'])) {
            $this->followRedirect = (bool) $config['followRedirect'];
        }
    }

    private function dummyRequest()
    {
        return $this->dummyRequest;
    }

    private function followRedirect()
    {
        return $this->followRedirect;
    }

    private function followResponseRedirect($response)
    {
        return
            $this->followRedirect()
            AND in_array($response['status'], array(301, 302, 303));
    }

    private function persistCookies($response)
    {
        if (empty($response['headers']['Set-Cookie'])) {
            return array();
        } else {
            $cookies = array();

            foreach ($response['headers']['Set-Cookie'] as $_cookie) {
                $cookies[$_cookie['name']] = $_cookie['value'];
            }

            return $cookies;
        }
    }

    public function run($handler, array $request = array())
    {
        $response = $handler($request + $this->dummyRequest());

        if ($this->followResponseRedirect($response)) {
            $response = $this->run($handler, array(
                'method' => 'GET',
                'uri' => $response['headers']['Location'],
                'headers' => array(
                    'cookies' => $this->persistCookies($response),
                ),
            ));
        }

        return $response;
    }
}
