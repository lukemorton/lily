<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Util;

class Response
{
    public static function response($status, $body = '', array $headers = array())
    {
        return compact('status', 'headers', 'body');
    }
    
    public static function ok($body = '', array $headers = array())
    {
        return static::response(200, $body, $headers);
    }

    public static function notFound($body = '', array $headers = array())
    {
        return static::response(404, $body, $headers);
    }
  
    public static function redirect($uri, $status = 302)
    {
        return static::response($status, '', array('Location' => $uri));
    }
  
    public static function redirectAfterPost($uri)
    {
        return static::redirect($uri, 303);
    }
}
