<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Middleware;

class Cookie
{
    private static function hash($request, $name, $value, $salt)
    {
        if (isset($request['headers']['user-agent'])) {
            $user_agent = $request['headers']['user-agent'];
        } else {
            $user_agent = '';
        }

        return sha1($user_agent.$name.$value.$salt);
    }

    public static function sign($request, $name, $value, $salt)
    {
        $hash = static::hash($request, $name, $value, $salt);
        return "{$hash}~{$value}";
    }

    public static function unsign($request, $name, $originalValue, $salt)
    {
        if (strpos($originalValue, '~') !== FALSE) {
            list($hash, $value) = explode('~', $originalValue);

            if ($hash === static::hash($request, $name, $value, $salt)) {
                return $value;
            }
        }
    }

    private $defaults = array();
    private $salt = 'a-salt';

    public function __construct($config = NULL)
    {
        if (isset($config['defaults'])) {
            $this->defaults = $config['defaults'];
        }

        if (isset($config['salt'])) {
            $this->salt = $config['salt'];
        }
    }

    public function __invoke($handler)
    {
        $defaults = $this->defaults;
        $salt = $this->salt;

        return function ($request) use ($handler, $defaults, $salt) {
            $request['cookies'] = array();

            if ( ! empty($request['headers']['cookies'])) {
                foreach ($request['headers']['cookies'] as $_name => $_value) {
                    if ($value = Cookie::unsign($request, $_name, $_value, $salt)) {
                        $request['cookies'][$_name] = $value;
                    }
                }
            }

            $response = $handler($request);

            if (isset($response['cookies'])) {
                if ( ! isset($response['headers']['Set-Cookie'])) {
                    $response['headers']['Set-Cookie'] = array();
                }

                foreach ($response['cookies'] as $_name => $_c) {
                    if ( ! is_array($_c)) {
                        $_c = array('value' => $_c);
                    }

                    if ($_c['value'] === NULL) {
                        $_c['expires'] = time() - (3600 * 24);
                    } else {
                        $_c['value'] =
                            Cookie::sign(
                                $request,
                                $_name,
                                $_c['value'],
                                $salt);
                    }

                    $response['headers']['Set-Cookie'][] =
                        array('name' => $_name) + $_c + $defaults;
                }

                unset($response['cookies']);
            }

            return $response;
        };
    }
}
