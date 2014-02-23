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

class CookieStore
{
    private $name = '_session';
    private $cookie = array();

    public function __construct($config = NULL)
    {
        if (isset($config['cookie'])) {
            if (isset($config['cookie']['name'])) {
                $this->name = $config['cookie']['name'];
                unset($config['cookie']['name']);
            }

            $this->cookie = $config['cookie'];
        }
    }

    public function get($request)
    {
        if (isset($request['cookies'][$this->name])) {
            $request['session'] =
                json_decode(
                    $request['cookies'][$this->name],
                    TRUE);
        }

        return $request;
    }

    public function set($response)
    {
        if (isset($response['session'])) {
            $response['cookies'][$this->name] =
                array('value' => json_encode($response['session']))
                + $this->cookie;
        }

        return $response;
    }
}
