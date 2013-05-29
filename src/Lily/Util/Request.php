<?php

namespace Lily\Util;

class Request
{
    public static function request($method, $uri = '', array $headers = array())
    {
        return compact('method', 'uri', 'headers');
    }
    
    public static function get($uri = '', array $headers = array())
    {
        return static::request('GET', $uri, $headers);
    }

    public static function post($uri = '', array $headers = array())
    {
        return static::request('POST', $uri, $headers);
    }
}
