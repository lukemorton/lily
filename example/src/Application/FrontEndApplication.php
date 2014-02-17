<?php

namespace Lily\Example\Application;

use Lily\Application\RoutedApplication;

use Lily\Example\Application\AdminApplication;

class FrontEndApplication extends RoutedApplication
{
    protected function routes()
    {
        return array(
            array('GET', '/', '<a href="/admin">admin'),
            $this->adminApplicationRoute(),
        );
    }

    // Send all request methods and any URL beginning with `/admin` to Admin
    private function adminApplicationRoute()
    {
        return array(NULL, '/admin(/**)', new AdminApplication);
    }
}
