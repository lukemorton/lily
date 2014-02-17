<?php

namespace Lily\Adapter;

use Lily\Util\Request;

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
    private $persistCookies;

    public function __construct($config = NULL)
    {
        if (isset($config['followRedirect'])) {
            $this->followRedirect = (bool) $config['followRedirect'];
        }

        if (isset($config['persistCookies'])) {
            $this->persistCookies = (bool) $config['persistCookies'];
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
        $isRedirectStatus = in_array($response['status'], array(301, 302, 303));
        return $this->followRedirect() AND $isRedirectStatus;
    }

    private function persistCookies($originalRequest, $response, $nextRequest)
    {
        if ($this->persistCookies) {
            if (isset($originalRequest['headers']['cookies'])) {
                $cookies = $originalRequest['headers']['cookies'];
            } else {
                $cookies = array();
            }

            if ( ! empty($response['headers']['Set-Cookie'])) {
                foreach ($response['headers']['Set-Cookie'] as $_cookie) {
                    $cookies[$_cookie['name']] = $_cookie['value'];
                }
            }

            $nextRequest['headers'] += compact('cookies');
        }

        return $nextRequest;
    }

    private function shallowMergeRequests($r1, $r2)
    {
        $mergedRequest = array();

        foreach (array($r1, $r2) as $_i => $request) {
            foreach ($request as $_k => $_v) {
                if (isset($mergedRequest[$_k]) AND is_array($mergedRequest[$_k])) {
                    $mergedRequest[$_k] += $_v;
                } else {
                    $mergedRequest[$_k] = $_v;
                }
            }
        }

        return $mergedRequest;
    }

    public function run($handler, array $request = array())
    {
        $originalRequest =
            $this->shallowMergeRequests(
                $this->dummyRequest(),
                $request);

        $response = $handler($originalRequest);

        if ($this->followResponseRedirect($response)) {
            $nextRequest =
                $this->shallowMergeRequests(
                    $this->dummyRequest(),
                    Request::get($response['headers']['Location']));

            $nextRequest =
                $this->persistCookies(
                    $originalRequest,
                    $response,
                    $nextRequest);

            $response = $this->run($handler, $nextRequest);
        }

        return $response;
    }
}
