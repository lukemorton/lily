<?php

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

    private $cookieDefaults = array();
    private $salt = 'a-salt';

    public function __construct(array $config = NULL)
    {
        if (isset($config['defaults'])) {
            $this->cookieDefaults = $config['defaults'];
        }

        if (isset($config['salt'])) {
            $this->salt = $config['salt'];
        }
    }

    public function __invoke($handler)
    {
        $cookieDefaults = $this->cookieDefaults;
        $salt = $this->salt;

        return function ($request) use ($handler, $cookieDefaults, $salt) {
            $request['cookies'] = array();

            foreach ($request['headers']['cookies'] as $_name => $_value) {
                if ($value = Cookie::unsign($request, $_name, $_value, $salt)) {
                    $request['cookies'][$_name] = $value;
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

                    $_c['value'] =
                        Cookie::sign($request, $_name, $_c['value'], $salt);

                    $response['headers']['Set-Cookie'][] =
                        array('name' => $_name) + $_c + $cookieDefaults;
                }

                unset($response['cookies']);
            }

            return $response;
        };
    }
}
