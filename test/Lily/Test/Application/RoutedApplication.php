<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Application;

use Lily\Test\Application\RoutableApplicationTest;

use Lily\Application\RoutedApplication;
use Lily\Mock\RoutedApplicationWithRoutes;

class RoutedApplicationTest extends RoutableApplicationTest
{
    protected function applicationWithRoutes()
    {
        return new RoutedApplicationWithRoutes;
    }

    protected function applicationWithoutRoutes(array $routes = NULL)
    {
        return new RoutedApplication($routes);
    }
}
