<?php

namespace Lily\Test\Application;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Middleware as MW;

use Lily\Util\Request;
use Lily\Util\Response;

class DescribeComplexApplication extends \PHPUnit_Framework_TestCase
{
    private function application()
    {
        return
            new MiddlewareApplication(array(
                new RoutedApplication(array(
                    array('GET', '/', '<a href="/admin">admin'),
                )),

                new MW\Cookie(array('salt' => 'random')),
            ));
    }

    public function testHomepage()
    {
        $app = $this->application();
        $response = $app(Request::get('/'));
        $this->assertContains('/admin', $response['body']);
    }
}
