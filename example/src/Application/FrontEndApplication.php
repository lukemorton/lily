<?php

namespace Lily\Example\Application;

use Lily\Application\RoutedApplication;

use Lily\Example\Application\AdminApplication;
use Lily\Example\Controller\MainController;

class FrontEndApplication extends RoutedApplication
{
    private function action($obj, $method)
    {
        return function ($request) use ($obj, $method) {
            return $obj->{$method}($request);
        };
    }

    protected function routes()
    {
        return array(
            array('GET', '/', $this->action(new MainController, 'index')),
            $this->adminApplicationRoute(),
        );
    }

    // Send all request methods and any URL beginning with `/admin` to Admin
    private function adminApplicationRoute()
    {
        return array(NULL, '/admin(/**)', new AdminApplication);
    }
}
