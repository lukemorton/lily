<?php

namespace Lily\Example\Interaction\Application;

use Lily\Application\WebApplication;

use Lily\Middleware\ExceptionHandler;
use Lily\Middleware\Injection;

class MainApplication extends WebApplication
{
    protected function routes()
    {
        return array(
            array('GET', '/', array('main', 'index')),

            // Send all request methods and any URL beginning with `/admin` to Admin
            array(NULL, '/admin(/**)', $this->application('admin')),

            array(NULL, NULL, array('main', 'notFound')),
        );
    }
    
    protected function middleware()
    {
        return
            array_merge(
                parent::middleware(),
                array(
                    new ExceptionHandler,
                ));
    }
}
