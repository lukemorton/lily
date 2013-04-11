<?php

namespace Lily\Mock;

use Lily\Util\RoutedApplication;

class RoutedApplicationWithRoutes extends RoutedApplication
{
    public function routes()
    {
        return array(
            'index' => array('GET', '/', 'index'),
            'slug' => array('GET', '/slug/:slug', 'slug'),
        );
    }
}
