<?php

namespace Lily\Example\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware as MW;

use Lily\Util\Response;

use Lily\Example\Controller\AdminController;

class AdminApplication extends MiddlewareApplication
{
    protected function middleware()
    {
        return array(
            $this->routedApplication(),
            $this->adminAuthMiddleware(),
            new MW\Cookie(array('salt' => 'random')),
        );
    }

    private function routedApplication()
    {
        return new RoutedApplication(array(
            array('GET', '/admin', $this->action('index')),

            array('GET',  '/admin/login', $this->action('login')),
            array('POST', '/admin/login', $this->action('login_process')),

            array('GET', '/admin/logout', $this->action('logout')),
        ));
    }

    private function action($action)
    {
        return function ($request) use ($action) {
            $controller = new AdminController;
            return $controller->{$action}($request);
        };
    }

    private function adminAuthMiddleware()
    {
        return function ($handler) {
            return function ($request) use ($handler) {
                $isLogin = $request['uri'] === '/admin/login';
                $isAuthed = isset($request['cookies']['authed']);

                if ($isLogin AND $isAuthed) {
                    return Response::redirect('/admin');
                } else if ( ! $isLogin AND ! $isAuthed) {
                    return Response::redirect('/admin/login');
                }

                return $handler($request);
            };
        };
    }
}
