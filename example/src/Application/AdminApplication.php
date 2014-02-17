<?php

namespace Lily\Example\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware as MW;

use Lily\Util\Response;

class AdminApplication extends MiddlewareApplication
{
    public function __construct(array $pipeline = NULL)
    {
        parent::__construct(array(
            $this->routedApplication(),
            $this->adminAuthMiddleware(),
            new MW\Cookie(array('salt' => 'random')),
        ));
    }

    private function routedApplication()
    {
        return new RoutedApplication(array(
            array('GET', '/admin', '<a href="/logout">logout'),

            array('GET', '/admin/login', '<form method="post"><button>Login'),

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
        ));
    }

    private function adminAuthMiddleware()
    {
        return function ($handler) {
            return function ($request) use ($handler) {
                if ($request['uri'] !== '/admin/login'
                    AND ! isset($request['cookies']['authed'])) {
                    return Response::redirect('/admin/login');
                }

                return $handler($request);
            };
        };
    }
}
