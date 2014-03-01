<?php

namespace Lily\Example\Interaction\Application;

use Lily\Application\WebApplication;

use Lily\Middleware\ExceptionHandler;
use Lily\Middleware\Injection;

use Lily\Example\Interaction\Application\AdminApplication;
use Lily\Example\Interaction\Controller\MainController;
use Lily\Example\Interaction\Controller\AdminController;

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
        return array(
            new ExceptionHandler,
            new Injection(array(
                'inject' => array(
                    'di' => array(
                        'interaction' => array(
                            'applications' => array(
                                'admin' => new AdminApplication,
                            ),
                            'controllers' => array(
                                'main' => new MainController,
                                'admin' => new AdminController,
                            ),
                        ),
                    ),
                ),
            )),
        );
    }
}
