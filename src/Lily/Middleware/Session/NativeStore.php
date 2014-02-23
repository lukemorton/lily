<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
