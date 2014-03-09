<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Application;

use Lily\Mock\WebApplication;
use Lily\Mock\WebAdminApplication;
use Lily\Mock\WebController;

use Lily\Util\Request;

class WebApplicationTest extends \PHPUnit_Framework_TestCase
{
    private function webApplication()
    {
        return new WebApplication(array(
            'inject' => array(
                'di' => array(
                    'interaction' => array(
                        'applications' => array(
                            'admin' => new WebAdminApplication,
                        ),
                        'controllers' => array(
                            'test' => new WebController,
                        ),
                    ),
                ),
            ),
        ));
    }

    public function testItShouldInjectControllers()
    {
        $app = $this->webApplication();
        $response = $app(Request::get('/') + array('params' => array()));
        $this->assertSame('index', $response['body']);
        $response = $app(Request::get('/slug/hmm') + array('params' => array()));
        $this->assertSame('hmm', $response['body']);
    }

    public function testItShouldInjectApplications()
    {
        $app = $this->webApplication();
        $response = $app(Request::get('/admin') + array('params' => array()));
        $this->assertSame('admin', $response['body']);
    }
}
