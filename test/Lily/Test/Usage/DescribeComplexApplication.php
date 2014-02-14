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
                )),

                function ($handler) {
                    return function ($request) use ($handler) {
                        if ( ! isset($request['cookies']['authed'])) {
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
                )),

                new MW\Cookie(array('salt' => 'random')),
            ));
    }

    public function testHomepage()
    {
        $app = $this->application();
        $response = $app(Request::get('/'));
        $this->assertContains('/admin', $response['body']);
    }

    public function testAdminRedirectsToLoginIfNotAuthed()
    {
        $app = $this->application();
        $response = $app(Request::get('/admin'));
        $this->assertSame('/admin/login', $response['headers']['Location']);
    }
}
