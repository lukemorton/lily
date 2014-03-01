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

use Lily\Application\RoutedApplication;

use Lily\Mock\RoutedApplicationWithRoutes;

use Lily\Util\Request;
use Lily\Util\Response;

abstract class RoutableApplicationTest extends \PHPUnit_Framework_TestCase
{
    private function request($method = NULL, $uri = NULL)
    {
        return compact('method', 'uri') + array(
            'params' => array(),
        );
    }

    abstract protected function applicationWithRoutes();

    abstract protected function applicationWithoutRoutes($routes = NULL);

    public function routesProvider()
    {
        $routes = array(
            array('GET', '/', 'GET /'),
            array('GET', '/slug/:a', 'GET /slug/:a'),
            array('GET', '/slug/:a/:b', 'GET /slug/:a/:b'),
            array('GET', '/slug/:a/sep/:b', 'GET /slug/:a/sep/:b'),
            array('GET', '/slug-opt/:a(/sep/:b)', 'GET /slug-opt/:a(/sep/:b)'),

            array('GET', '/user/*', 'GET /user/*'),
            array('GET', '/admin(/**)', 'GET /admin(/**)'),
            array('GET', '/resource/*.*', 'GET /resource/*.*'),

            array('HEAD', '/', 'HEAD /'),
            array('POST', '/', 'POST /'),
            array('PUT', '/', 'PUT /'),
            array('DELETE', '/', 'DELETE /'),
        );

        shuffle($routes);

        $routes[] = array('GET', '/params/:a', function ($request) {
            return "GET /params/{$request['params']['a']}";
        });

        $routes[] = array('GET', '/:a', function ($request) {
            return "GET /{$request['params']['a']}";
        });

        $app = $this->applicationWithoutRoutes($routes);

        return array(
            array($app, 'GET', '/', 'GET /'),
            array($app, 'GET', '/slug/a', 'GET /slug/:a'),
            array($app, 'GET', '/slug/a/b', 'GET /slug/:a/:b'),
            array($app, 'GET', '/slug/a/sep/b', 'GET /slug/:a/sep/:b'),
            array($app, 'GET', '/slug-opt/a', 'GET /slug-opt/:a(/sep/:b)'),
            array($app, 'GET', '/slug-opt/a/sep/b', 'GET /slug-opt/:a(/sep/:b)'),

            array($app, 'GET', '/user/anything', 'GET /user/*'),
            array($app, 'GET', '/admin', 'GET /admin(/**)'),
            array($app, 'GET', '/admin/login', 'GET /admin(/**)'),
            array($app, 'GET', '/admin/support/tickets', 'GET /admin(/**)'),
            array($app, 'GET', '/admin/support/tickets/11', 'GET /admin(/**)'),
            array($app, 'GET', '/resource/users.json', 'GET /resource/*.*'),

            array($app, 'HEAD', '/', 'HEAD /'),
            array($app, 'POST', '/', 'POST /'),
            array($app, 'PUT', '/', 'PUT /'),
            array($app, 'DELETE', '/', 'DELETE /'),

            array($app, 'GET', '/params/else', 'GET /params/else'),
            array($app, 'GET', '/else', 'GET /else'),
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
            array($this->applicationWithRoutes()),
            array($this->applicationWithoutRoutes(array(
                array('GET', '/', array(200, array(), 'index')),
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

        $expectedApp = $this->applicationWithoutRoutes(array(
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
        $handler = $this->applicationWithoutRoutes();
        $this->assertSame(
            Response::notFound(),
            $handler($this->request()));
    }

    public function testReverseRouting()
    {
        $app = $this->applicationWithRoutes();
        $this->assertSame('/', $app->uri('index'));
        $this->assertSame(
            '/slug/test',
            $app->uri('slug', array('slug' => 'test')));
    }

    public function testFalseReturnedFromHandlerPassesRequestToNextMatch()
    {
        $handler =
            $this->applicationWithoutRoutes(array(
                array('GET', '/', FALSE),
                array('GET', '/', array(200, array(), 'hey')),
            ));

        $response = $handler(Request::get('/'));
        $this->assertSame('hey', $response['body']);
    }
}
