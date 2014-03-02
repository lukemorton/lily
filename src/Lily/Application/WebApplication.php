<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Application;

use Lily\Application\RoutedApplication;
use Lily\Application\MiddlewareApplication;

use Lily\Middleware\Injection;

/**
 * An application handler for most web applications
 */
abstract class WebApplication
{
    private $inject = array();

    public function __construct($config = NULL)
    {
        if (isset($config['inject'])) {
            $this->inject = $config['inject'];
        }
    }

    private function inject()
    {
        return $this->inject;
    }

    private function parseResponse($route)
    {
        $response = $route[2];
        
        if (is_array($response) AND count($response) === 2) {
            list($controller, $action) = $response;
            $response = function ($request) use ($controller, $action) {
                $controllers = $request['di']['interaction']['controllers'];
                return $controllers[$controller]->{$action}($request);
            };
        }

        $route[2] = $response;
        return $route;
    }

    private function parseRoutes($routes)
    {
        $parsedRoutes = array();

        foreach ($routes as $_k => $_route)
        {
            $parsedRoutes[$_k] = $this->parseResponse($_route);
        }

        return $parsedRoutes;
    }

    private function routedApplication()
    {
        $routes = $this->parseRoutes($this->routes());
        return new RoutedApplication(compact('routes'));
    }

    private function middlewareApplication()
    {
        return new MiddlewareApplication(array(
            'handler' => $this->routedApplication(),
            'middleware' => $this->middleware(),
        ));
    }

    abstract protected function routes();

    protected function middleware()
    {
        return array(
            new Injection(array('inject' => $this->inject())),
        );
    }

    protected function applicationHandler($application)
    {
        return function ($request) use ($application) {
            $applications = $request['di']['interaction']['applications'];
            return $applications[$application]($request);
        };
    }

    public function __invoke($request)
    {
        $handler = $this->middlewareApplication();
        return $handler($request);
    }
}
