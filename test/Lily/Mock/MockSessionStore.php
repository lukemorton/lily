<?php

namespace Lily\Mock;

use Lily\Application\RoutedApplication;

class MockSessionStore
{
    public $session;

    public function set(array $response)
    {
        if (isset($response['session'])) {
            $this->session = $response['session'];
        }

        return $response;
    }

    public function get(array $request)
    {
        $request['session'] = $this->session;
        return $request;;
    }
}
