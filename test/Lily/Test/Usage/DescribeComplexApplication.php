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
                    array('GET', '/admin', $admin),
                    array('GET', '/admin/login', $admin),
                    array('POST', '/admin/login', $admin),
                )),

                new MW\Cookie(array('salt' => 'random')),
            ));
    }

    private function applicationResponse($url)
    {
        $app = $this->application();
        return $app(Request::get($url));
    }

    private function applicationFormResponse($url)
    {
        $app = $this->application();
        return $app(Request::post($url));
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
}
