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
            array(NULL, '/admin(/**)', new AdminApplication),
        );
    }
}
