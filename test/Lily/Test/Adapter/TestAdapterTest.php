<?php

namespace Lily\Test\Adapter;

use Lily\Adapter\Test;

use Lily\Util\Response;

class TestAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldCallHandlerWithDummyRequest()
    {
        $application =
            function ($request) use (& $actualRequest) {
                $actualRequest = $request;
                return Response::ok();
            };

        $test_adapter = new Test;
        $test_adapter->run($application);

        $this->assertSame('GET', $actualRequest['method']);
        $this->assertSame('/', $actualRequest['uri']);
    }

    public function testItShouldReturnResponse()
    {
        $application =
            function ($request) {
                return Response::ok();
            };

        $test_adapter = new Test;
        $response = $test_adapter->run($application);

        $this->assertSame(200, $response['status']);
    }

    private function redirectApplication()
    {
        return
            function ($request) {
                if ($request['uri'] === '/') {
                    return Response::redirect('/redirected');
                } else {
                    return Response::ok();
                }
            };
    }

    public function testItShouldFollowRedirectsIfEnabled()
    {
        $test_adapter = new Test(array('followRedirect' => TRUE));
        $response = $test_adapter->run($this->redirectApplication());
        $this->assertSame(200, $response['status']);
    }

    public function testItShouldNotFollowRedirectsIfNotEnabled()
    {
        $test_adapter = new Test;
        $response = $test_adapter->run($this->redirectApplication());
        $this->assertSame(302, $response['status']);
    }
}
