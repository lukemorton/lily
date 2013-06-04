<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\Cookie;

use Lily\Util\Response as Res;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function testCookieKeyAddedToRequest()
    {
        $mw = new Cookie;
        $actualRequest = NULL;

        $wrappedHandler =
            $mw(
                function ($request) use (& $actualRequest) {
                    $actualRequest = $request;
                    return Res::ok();
                });

        $expectedCookies = array('a' => 1);

        $response =
            $wrappedHandler(
                array(
                    'headers' => array('cookies' => $expectedCookies),
                ));

        $this->assertSame($expectedCookies, $actualRequest['cookies']);
    }

    private function signed($name, $value, $salt)
    {
        return Cookie::sign(NULL, $name, $value, $salt);
    }

    public function cookieProviders()
    {
        $salt = 'cool';

        return array(
            array(
                array('secure' => TRUE),
                array('a' => 1),
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $salt),
                        'secure' => TRUE,
                    ),
                ),
            ),
            array(
                array('secure' => TRUE),
                array(
                    'a' => 1,
                    'b' => array('value' => 2),
                ),
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $salt),
                        'secure' => TRUE,
                    ),

                    array(
                        'name' => 'b',
                        'value' => $this->signed('b', 2, $salt),
                        'secure' => TRUE,
                    ),
                ),
            ),
            array(
                array('secure' => TRUE),
                array(
                    'a' => array('value' => 1),
                ),
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $salt),
                        'secure' => TRUE
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider cookieProviders
     */
    public function testCookieDefaults($defaults, $cookies, $salt, $expected)
    {
        $mw = new Cookie(compact('defaults', 'salt'));

        $wrappedHandler =
            $mw(
                function ($request) use ($cookies) {
                    return Res::ok() + compact('cookies');
                });

        $response =
            $wrappedHandler(array(
                'headers' => array('cookies' => array()),
            ));

        $this->assertSame($expected, $response['headers']['Set-Cookie']);
    }
}
