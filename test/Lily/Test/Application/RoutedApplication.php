<?php

namespace Lily\Test\Application;

use Lily\Application\RoutedApplication;
use Lily\Mock\RoutedApplicationWithRoutes;
use Lily\Util\Response;

class RoutedApplicationTest extends \PHPUnit_Framework_TestCase
{
    private function request($method = NULL, $uri = NULL)
    {
        return compact('method', 'uri') + array(
            'params' => array(),
        );
    }

    private function routedApplicationWithRoutes()
    {
        return new RoutedApplicationWithRoutes;
    }

    private function routedApplicationWithoutRoutes(array $routes = NULL)
    {
        return new RoutedApplication($routes);
    }

    public function routesProvider()
    {
        $app = $this->routedApplicationWithoutRoutes(array(
            array('GET', '/', 'GET /'),
            array('GET', '/slug/:a', 'GET /slug/:a'),
            array('GET', '/slug/:a/:b', 'GET /slug/:a/:b'),
            array('GET', '/slug/:a/sep/:b', 'GET /slug/:a/sep/:b'),
            array('GET', '/slug-opt/:a(/sep/:b)', 'GET /slug-opt/:a(/sep/:b)'),

            array('HEAD', '/', 'HEAD /'),
            array('POST', '/', 'POST /'),
            array('PUT', '/', 'PUT /'),
            array('DELETE', '/', 'DELETE /'),
        ));

        return array(
            array($app, 'GET', '/', 'GET /'),
            array($app, 'GET', '/slug/a', 'GET /slug/:a'),
            array($app, 'GET', '/slug/a/b', 'GET /slug/:a/:b'),
            array($app, 'GET', '/slug/a/sep/b', 'GET /slug/:a/sep/:b'),
            array($app, 'GET', '/slug-opt/a', 'GET /slug-opt/:a(/sep/:b)'),
            array($app, 'GET', '/slug-opt/a/sep/b', 'GET /slug-opt/:a(/sep/:b)'),

            array($app, 'HEAD', '/', 'HEAD /'),
            array($app, 'POST', '/', 'POST /'),
            array($app, 'PUT', '/', 'PUT /'),
            array($app, 'DELETE', '/', 'DELETE /'),
        );
    }

    /**
     * @dataProvider  routesProvider
     */
    public function testRouteMatching($handler, $method, $uri, $expected)
    {
        $this->assertSame(
            Response::ok($expected),
            $handler($this->request($method, $uri)));
    }

    public function routedApplicationProvider()
    {
        return array(
            array($this->routedApplicationWithRoutes()),
            array($this->routedApplicationWithoutRoutes(array(
                array('GET', '/', 'index'),
            ))),
        );
    }

    /**
     * @dataProvider routedApplicationProvider
     */
    public function testResponseReturnedFromAppHandler($handler)
    {
        $this->assertSame(
            Response::ok('index'),
            $handler($this->request('GET', '/')));
    }

    public function testAppAddedToRequest()
    {
        $actualApp = FALSE;

        $expectedApp = $this->routedApplicationWithoutRoutes(array(
            array(NULL, NULL, function ($request) use (& $actualApp) {
                $actualApp = $request['app'];
                return '';
            }),
        ));
        $expectedApp($this->request('GET', '/'));

        $this->assertSame($expectedApp, $actualApp);
    }

    public function testNotFoundRoute()
    {
        $handler = $this->routedApplicationWithoutRoutes();
        $this->assertSame(
            Response::notFound(),
            $handler($this->request()));
    }

    public function testReverseRouting()
    {
        $app = $this->routedApplicationWithRoutes();
        $this->assertSame('/', $app->uri('index'));
        $this->assertSame(
            '/slug/test',
            $app->uri('slug', array('slug' => 'test')));
    }
}
