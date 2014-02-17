<?php

namespace Lily\Example;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware as MW;

use Lily\Util\Response;

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

        new MW\Cookie(array('salt' => 'random')),
    ));

return
    new RoutedApplication(array(
        array('GET', '/', '<a href="/admin">admin'),
        array(NULL, '/admin(/**)', $admin),
    ));
