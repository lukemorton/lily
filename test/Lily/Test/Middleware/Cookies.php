<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\Cookies;

use Lily\Util\Response as Res;

class CookiesTest extends \PHPUnit_Framework_TestCase
{
    public function testCookieKeyAddedToRequest()
    {
        $mw = new Cookies;
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
    public function cookieProviders()
    {
        return array(
            array(
                array('secure' => TRUE),
                array('a' => 1),
                array(
                    array('name' => 'a', 'value' => 1, 'secure' => TRUE),
                ),
            ),
            array(
                array('secure' => TRUE),
                array(
                    'a' => 1,
                    'b' => array('value' => 2),
                ),
                array(
                    array('name' => 'a', 'value' => 1, 'secure' => TRUE),
                    array('name' => 'b', 'value' => 2, 'secure' => TRUE),
                ),
            ),
            array(
                array('secure' => TRUE),
                array(
                    'a' => array('value' => 1),
                ),
                array(
                    array('name' => 'a', 'value' => 1, 'secure' => TRUE),
                ),
            ),
        );
    }

    /**
     * @dataProvider cookieProviders
     */
    public function testCookieDefaults($defaults, $cookies, $expected)
    {
        $mw = new Cookies(compact('defaults'));

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
