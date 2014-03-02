<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Adapter;

use Lily\Adapter\Test;

use Lily\Application\MiddlewareApplication;

use Lily\Middleware\Cookie;

use Lily\Util\Response;

class TestAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldCallHandlerWithDummyRequest()
    {
        $application =
            function ($request) use (& $actualRequest) {
                $actualRequest = $request;
                return Response::ok();
            };

        $test_adapter = new Test;
        $test_adapter->run($application);

        $this->assertSame('GET', $actualRequest['method']);
        $this->assertSame('/', $actualRequest['uri']);
    }

    public function testItShouldReturnResponse()
    {
        $application =
            function ($request) {
                return Response::ok();
            };

        $test_adapter = new Test;
        $response = $test_adapter->run($application);

        $this->assertSame(200, $response['status']);
    }

    private function redirectApplication()
    {
        return
            function ($request) {
                if ($request['uri'] === '/') {
                    return Response::redirect('/redirected');
                } else {
                    return Response::ok();
                }
            };
    }

    public function testItShouldFollowRedirectsIfEnabled()
    {
        $test_adapter = new Test(array('followRedirect' => TRUE));
        $response = $test_adapter->run($this->redirectApplication());
        $this->assertSame(200, $response['status']);
    }

    public function testItShouldNotFollowRedirectsIfNotEnabled()
    {
        $test_adapter = new Test;
        $response = $test_adapter->run($this->redirectApplication());
        $this->assertSame(302, $response['status']);
    }

    private function cookieApplication()
    {
        return
            new MiddlewareApplication(array(
                'handler' => function ($request) {
                    if ($request['uri'] === '/') {
                        return Response::redirect('/redirected') + array(
                            'cookies' => array('a' => 'cookie'),
                        );
                    } else if (isset($request['cookies']['a'])) {
                        return Response::ok($request['cookies']['a']);
                    } else {
                        return Response::ok('no cookie');
                    }
                },

                'middleware' => array(
                    new Cookie(array('salt' => 'cool')),
                ),
            ));
    }

    public function testItShouldPersistCookiesIfEnabled()
    {
        $test_adapter = new Test(array(
            'persistCookies' => TRUE,
            'followRedirect' => TRUE,
        ));
        $response = $test_adapter->run($this->cookieApplication());
        $this->assertSame('cookie', $response['body']);
    }

    public function testItShouldNotPersistCookiesIfNotEnabled()
    {
        $test_adapter = new Test(array(
            'followRedirect' => TRUE,
        ));
        $response = $test_adapter->run($this->cookieApplication());
        $this->assertSame('no cookie', $response['body']);
    }

    private function authedCookie()
    {
        $headers = array('user-agent' => 'Lily\Adapter\Test');
        return Cookie::sign(compact('headers'), 'authed', 'yes', 'test');
    }

    private function authedCookieRequest()
    {
        return array(
            'headers' => array(
                'cookies' => array('authed' => $this->authedCookie()),
            ),
        );
    }

    public function testItShouldPersistCookiesEvenIfNotSet()
    {
        $test_adapter = new Test(array(
            'persistCookies' => TRUE,
            'followRedirect' => TRUE,
        ));

        $cookieMiddleware = new Cookie(array('salt' => 'test'));

        $response =
            $test_adapter->run(
                $cookieMiddleware(
                    function ($request) {
                        if (isset($request['cookies']['authed'])) {
                            if ($request['uri'] === '/') {
                                return Response::redirect('/logged-in');
                            } else {
                                return Response::ok($request['cookies']['authed']);
                            }
                        } else {
                            return Response::notFound();
                        }
                    }),
                $this->authedCookieRequest());

        $this->assertSame('yes', $response['body']);
    }
}
