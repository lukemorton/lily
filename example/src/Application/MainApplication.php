<?php

namespace Lily\Example\Application;

use Lily\Application\RoutedApplication;

use Lily\Example\Application\AdminApplication;
use Lily\Example\Controller\MainController;

class MainApplication extends RoutedApplication
{
    private function action($action)
    {
        return function ($request) use ($action) {
            $controller = new MainController;
            return $controller->{$action}($request);
        };
    }

    protected function routes()
    {
        return array(
            array('GET', '/', $this->action('index')),
            $this->adminApplicationRoute(),
        );
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
