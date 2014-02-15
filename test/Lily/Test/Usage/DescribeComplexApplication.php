<?php

namespace Lily\Test\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware as MW;

use Lily\Util\Request;
use Lily\Util\Response;

class DescribeComplexApplication extends \PHPUnit_Framework_TestCase
{
    private function application()
    {
        $admin =
            new MiddlewareApplication(array(
                new RoutedApplication(array(
                    array('GET', '/admin', '<a href="/logout">logout'),

                    array('GET', '/admin/login', '<form action="post"><button>Login'),

                    array('POST', '/admin/login', function () {
                        return Response::redirect('/admin') + array(
                            'cookies' => array('authed' => TRUE),
                        );
                    }),

                    array('GET', '/admin/logout', function () {
                        return Response::redirect('/admin/login') + array(
                            'cookies' => array('authed' => NULL),
                        );
                    }),
                )),

                function ($handler) {
                    return function ($request) use ($handler) {
                        if ($request['uri'] !== '/admin/login'
                            AND ! isset($request['cookies']['authed'])) {
                            return Response::redirect('/admin/login');
                        }

                        return $handler($request);
                    };
                },
            ));

        return
            new MiddlewareApplication(array(
                new RoutedApplication(array(
                    array('GET', '/', '<a href="/admin">admin'),
                    array(NULL, '/admin(/**)', $admin),
                )),

                new MW\Cookie(array('salt' => 'random')),
            ));
    }

    private function applicationResponse($url, $request = array())
    {
        $app = $this->application();
        return $app($request + Request::get($url));
    }

    private function applicationFormResponse($url)
    {
        $app = $this->application();
        return $app(Request::post($url));
    }

    private function preserveCookies($response)
    {
        if (empty($response['headers']['Set-Cookie'])) {
            return array();
        } else {
            $cookies = array();

            foreach ($response['headers']['Set-Cookie'] as $_cookie) {
                $cookies[$_cookie['name']] = $_cookie['value'];
            }

            return $cookies;
        }
    }

    private function followResponse($response)
    {
        if ( ! isset($response['headers']['Location'])) {
            return $response;
        }

        $request = Request::get($response['headers']['Location']);
        $request['headers']['cookies'] = $this->preserveCookies($response);

        $app = $this->application();
        return $app($request);
    }

    public function testHomepage()
    {
        $response = $this->applicationResponse('/');
        $this->assertContains('/admin', $response['body']);
    }

    public function testAdminRedirectsToLoginIfNotAuthed()
    {
        $response = $this->applicationResponse('/admin');
        $this->assertSame('/admin/login', $response['headers']['Location']);
    }

    public function testAdminRedirectsToAdminOnLogin()
    {
        $response = $this->applicationFormResponse('/admin/login');
        $this->assertSame('/admin', $response['headers']['Location']);
    }

    public function testAdminLogsInSuccessfully()
    {
        $response =
            $this->followResponse(
                $this->applicationFormResponse('/admin/login'));
        $this->assertContains('/logout', $response['body']);
    }

    public function testAdminStaysLoggedIn()
    {
        $response =
            $this->applicationResponse('/admin', array(
                'headers' => array(
                    'cookies' => array(
                        'authed' => MW\Cookie::sign(array(), 'authed', TRUE, 'random'),
                    ),
                ),
            ));
        $this->assertContains('/logout', $response['body']);
    }

    public function testAdminLogsOutSuccessfully()
    {
        $response =
            $this->followResponse(
                $this->applicationResponse('/admin/logout', array(
                    'cookies' => array(
                        'authed' => MW\Cookie::sign(array(), 'authed', TRUE, 'random'),
                    ),
                )));
        $this->assertContains('Login', $response['body']);
    }
}
