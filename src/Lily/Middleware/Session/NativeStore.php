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

    public function get($request)
    {
        $request['session'] = $_SESSION;
        return $request;
    }

    public function set($response)
    {
        if (isset($response['session'])) {
            $_SESSION = $response['session'];
        }
        
        return $response;
    }
}
