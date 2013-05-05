<?php

namespace Lily\Test\Middleware;

use Lily\Middleware\ResponseStatusHandler;

use Lily\Util\Response;

class ResponseStatusHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function statusHandlerMapProvider()
    {
        $mapWith404And500 = array(
            404 => function () {
                return Response::response(404, 'not found');
            },
            500 => function () {
                return Response::response(500, 'error');
            },
        );

        return array(
            array(
                500,
                'error',
                array(
                    500 => function () {
                        return Response::response(500, 'error');
                    },
                ),
            ),

            array(500, 'error', $mapWith404And500),
            array(404, 'not found', $mapWith404And500),
            array(200, 'cool', $mapWith404And500),
        );
    }

    /**
     * @dataProvider statusHandlerMapProvider
     */
    public function testResponseStatusesHandled($expectedStatus, $expectedBody, $map)
    {
        $wrapHandler = new ResponseStatusHandler($map);

        $wrappedHandler =
            $wrapHandler(
                function ($request) use ($expectedStatus, $expectedBody) {
                    return Response::response($expectedStatus, $expectedBody);
                });

        $response = $wrappedHandler(array());
        $this->assertSame($expectedStatus, $response['status']);
        $this->assertSame($expectedBody, $response['body']);
    }

    public function testOriginalResponseAddedToRequest()
    {
        $actualRequest = NULL;

        $wrapHandler =
            new ResponseStatusHandler(array(
                500 => function ($request) use (&$actualRequest) {
                    $actualRequest = $request;
                    return Response::response(500);
                },
            ));

        $wrappedHandler =
            $wrapHandler(
                function ($request) {
                    return Response::response(500);
                });

        $response = $wrappedHandler(array());
        $this->assertArrayHasKey('original-response', $actualRequest);
    }
}
