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
}
