<?php

namespace Lily\Middleware\Session;

class NativeStore
{
    public function get()
    {
        return $_SESSION;
    }

    public function set(array $session)
    {
        $_SESSION = $session;
    }
}
