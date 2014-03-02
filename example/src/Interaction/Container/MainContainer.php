<?php

namespace Lily\Example\Interaction\Container;

use Lily\Example\Interaction\Application\MainApplication;
use Lily\Example\Interaction\Application\AdminApplication;
use Lily\Example\Interaction\Controller\MainController;
use Lily\Example\Interaction\Controller\AdminController;

class MainContainer
{
    /**
     * Initialise and return MainApplication.
     */
    public function application()
    {
        return new MainApplication(array(
            'inject' => $this->dependencies(),
        ));
    }

    private function dependencies()
    {
        return array(
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
        );
    }
}
