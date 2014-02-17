<?php

namespace Lily\Test\Application;

use Lily\Adapter\Test;

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

    private function runApplication($application, $request)
    {
        $testAdapter = new Test(array(
            'followRedirect' => TRUE,
            'persistCookies' => TRUE,
        ));
        return $testAdapter->run($application, $request);
    }

    private function applicationResponse($url, $request = array())
    {
        return
            $this->runApplication(
                $this->application(),
                $request + Request::get($url));
    }

    private function applicationFormResponse($url)
    {
        return
            $this->runApplication(
                $this->application(),
                Request::post($url));
    }

    public function testHomepage()
    {
        $response = $this->applicationResponse('/');
        $this->assertContains('/admin', $response['body']);
    }

    public function testAdminRedirectsToLoginIfNotAuthed()
    {
        $response = $this->applicationResponse('/admin');
        $this->assertContains('Login', $response['body']);
    }

    public function testAdminRedirectsToAdminOnLogin()
    {
        $response = $this->applicationFormResponse('/admin/login');
        $this->assertContains('logout', $response['body']);
    }

    private function authedCookie()
    {
        $headers = array('user-agent' => 'Lily\Adapter\Test');
        return MW\Cookie::sign(compact('headers'), 'authed', TRUE, 'random');
    }

    private function authedCookieRequest()
    {
        return array(
            'headers' => array(
                'cookies' => array('authed' => $this->authedCookie()),
            ),
        );
    }

    public function testAdminStaysLoggedIn()
    {
        $response = $this->applicationResponse('/admin', $this->authedCookieRequest());
        $this->assertContains('/logout', $response['body']);
    }

    public function testAdminLogsOutSuccessfully()
    {
        $response = $this->applicationResponse('/admin/logout', $this->authedCookieRequest());
        $this->assertContains('Login', $response['body']);
    }
}
