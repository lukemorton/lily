<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Middleware;

use Lily\Middleware\Cookie;

use Lily\Util\Response as Res;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    private function signed($name, $value, $userAgent, $salt)
    {
        return
            Cookie::sign(
                array(
                    'headers' => array('user-agent' => $userAgent),
                ),
                $name,
                $value,
                $salt);
    }

    public function cookieReadProvider()
    {
        $salt = 'bob';
        $userAgent = 'foo';

        return array(
            array(
                array('a' => '1'),
                array('a' => $this->signed('a', '1', $userAgent, $salt)),
                $userAgent,
                $salt,
            ),
            array(
                array('a' => '1'),
                array('a' => $this->signed('a', '1', NULL, $salt)),
                NULL,
                $salt,
            ),
            array(
                array(),
                array('a' => 'invalid'),
                $userAgent,
                $salt,
            ),
            array(
                array(),
                array('a' => 'invalid~1'),
                $userAgent,
                $salt,
            ),
        );
    }

    /**
     * @dataProvider cookieReadProvider
     */
    public function testCookieKeyAddedToRequest(
        $expectedCookies,
        $signedCookies,
        $userAgent,
        $salt)
    {
        $mw = new Cookie(compact('salt'));
        $actualRequest = NULL;

        $wrappedHandler =
            $mw(
                function ($request) use (& $actualRequest) {
                    $actualRequest = $request;
                    return Res::ok();
                });

        $response =
            $wrappedHandler(
                array(
                    'headers' => array(
                        'user-agent' => $userAgent,
                        'cookies' => $signedCookies,
                    ),
                ));

        $this->assertSame($expectedCookies, $actualRequest['cookies']);
    }

    public function cookieProviders()
    {
        $userAgent = 'bob';
        $salt = 'cool';

        return array(
            array(
                array('secure' => TRUE),
                array('a' => 1),
                $userAgent,
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $userAgent, $salt),
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
                $userAgent,
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $userAgent, $salt),
                        'secure' => TRUE,
                    ),

                    array(
                        'name' => 'b',
                        'value' => $this->signed('b', 2, $userAgent, $salt),
                        'secure' => TRUE,
                    ),
                ),
            ),
            array(
                array('secure' => TRUE),
                array(
                    'a' => array('value' => 1),
                ),
                $userAgent,
                $salt,
                array(
                    array(
                        'name' => 'a',
                        'value' => $this->signed('a', 1, $userAgent, $salt),
                        'secure' => TRUE
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider cookieProviders
     */
    public function testCookieDefaults(
        $defaults,
        $cookies,
        $userAgent,
        $salt,
        $expected)
    {
        $mw = new Cookie(compact('defaults', 'salt'));

        $wrappedHandler =
            $mw(
                function ($request) use ($cookies) {
                    return Res::ok() + compact('cookies');
                });

        $response =
            $wrappedHandler(array(
                'headers' => array(
                    'user-agent' => $userAgent,
                ),
            ));

        $this->assertSame($expected, $response['headers']['Set-Cookie']);
    }
}
