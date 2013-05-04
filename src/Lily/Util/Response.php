<?php

namespace Lily\Util;

class Response
{
    public static function response($status, array $headers = array(), $body = '')
    {
        return compact('status', 'headers', 'body');
    }
    
    public static function ok(array $headers = array(), $body = '')
    {
        return static::response(200, $headers, $body);
    }

    public static function notFound(array $headers = array(), $body = NULL)
    {
        if ($body === NULL) {
            $body = 'Not found.';
        }

        return static::response(404, $headers, $body);
    }
  
    public static function redirect($uri, $status = 302)
    {
        return static::response($status, array('Location' => $uri));
    }
  
    public static function redirectAfterPost($uri)
    {
        return static::redirect($uri, 303);
    }
}
