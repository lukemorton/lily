<?php

namespace Lily\Middleware\Session;

class NativeStore
{
    public function __construct()
    {
        if ( ! session_id()) {
            session_start();
        }
    }

    public function get(array $request)
    {
        $request['session'] = $_SESSION;
        return $request;
    }

    public function set(array $response)
    {
        if (isset($response['session'])) {
            $_SESSION = $response['session'];
        } else {
            session_destroy();
        }
        
        return $response;
    }
}
