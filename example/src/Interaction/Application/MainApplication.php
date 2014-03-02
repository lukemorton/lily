<?php

namespace Lily\Example\Interaction\Application;

use Lily\Application\WebApplication;

use Lily\Middleware\ExceptionHandler;
use Lily\Middleware\Injection;

class MainApplication extends WebApplication
{
    /**
     * Here we have three routes defined.
     *
     *  - First is the index route that runs main::index action
     *  - Next comes the admin application mounted as /admin
     *  - Last a match all route for displaying a 404 page using main::notFound
     */
    protected function routes()
    {
        return array(
            array('GET', '/', array('main', 'index')),
            array(NULL, '/admin(/**)', $this->application('admin')),
            array(NULL, NULL, array('main', 'notFound')),
        );
    }
    
    /**
     * Merge in ExceptionHandler with WebApplication::middleware().
     */
    protected function middleware()
    {
        $middleware = array(new ExceptionHandler);
        return array_merge(parent::middleware(), $middleware);
    }
}
