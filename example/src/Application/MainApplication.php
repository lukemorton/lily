<?php

namespace Lily\Example\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware\ExceptionHandler;

use Lily\Example\Application\AdminApplication;
use Lily\Example\Controller\MainController;

class MainApplication extends MiddlewareApplication
{
    protected function handler()
    {
        return new RoutedApplication(array('routes' => $this->routes()));
    }

    private function routes()
    {
        return array(
            array('GET', '/', $this->action('index')),
            $this->adminApplicationRoute(),
            array(NULL, NULL, $this->action('notFound')),
        );
    }
    
    protected function middleware()
    {
        return array(
            new ExceptionHandler,
        );
    }

    private function action($action)
    {
        return function ($request) use ($action) {
            $controller = new MainController;
            return $controller->{$action}($request);
        };
    }

    // Send all request methods and any URL beginning with `/admin` to Admin
    private function adminApplicationRoute()
    {
        return array(NULL, '/admin(/**)', function ($request) {
            $admin = new AdminApplication;
            return $admin($request);
        });
    }
}
