<?php

namespace Lily\Example\Interaction\Application;

use Lily\Application\WebApplication;

use Lily\Middleware as MW;

use Lily\Util\Response;

class AdminApplication extends WebApplication
{
    protected function routes()
    {
        return array(
            array('GET', '/admin', array('admin', 'index')),

            array('GET',  '/admin/login', array('admin', 'login')),
            array('POST', '/admin/login', array('admin', 'login_process')),

            array('GET', '/admin/logout', array('admin', 'logout')),
        );
    }
    
    protected function middleware()
    {
        return array(
            $this->ensureAuthenticated(),
            new MW\Cookie(array('salt' => 'random')),
        );
    }

    private function ensureAuthenticated()
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
