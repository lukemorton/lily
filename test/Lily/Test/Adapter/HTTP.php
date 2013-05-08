<?php

namespace Lily\Test\Adapter;

use Lily\Adapter\HTTP;
use Lily\Util\Response;

class HTTPTest extends \PHPUnit_Framework_TestCase
{
    private $oldServer;
    private $oldCookies;
    private $oldGet;
    private $oldPost;
    private $oldFiles;

    private function serverData()
    {
        return array(
            array('host',        'SERVER_NAME',     'localhost', NULL),
            array('port',        'SERVER_PORT',     80,          NULL),
            array('addr',        'SERVER_ADDR',     '127.0.0.1', NULL),
            array('remote-addr', 'REMOTE_ADDR',     '127.0.0.1', NULL),
            array('scheme',      'SERVER_PROTOCOL', 'HTTP/1.1',  'http'),
            array('method',      'REQUEST_METHOD',  'GET',       'GET'),
            array('uri',         'PATH_INFO',       '/',         NULL),
            array('type',        'CONTENT_TYPE',    'text/html', NULL),
            array('length',      'CONTENT_LENGTH',  100,         NULL),
        );
    }

    private function serverHeaderData()
    {
        return array(
            array('content-type',   'CONTENT_TYPE',    'text/html', NULL),
            array('content-length', 'CONTENT_LENGTH',  100,         NULL),
            array('x-foo-bar',      'HTTP_X_FOO_BAR',  'COOL',      NULL),
        );
    }

    private function responseData()
    {
        return Response::ok('test this', array(
            'Content-Type' => 'text/plain',
            'Set-Cookie' => array(
                array('name' => 'a', 'value' => 1),
            ),
        ));
    }

    private function setUpGlobalRequestData()
    {
        $serverData = array_merge(
            $this->serverData(),
            $this->serverHeaderData());

        $this->oldServer = $_SERVER;

        foreach ($serverData as $_d) {
            list($_, $k, $v) = $_d;
            $_SERVER[$k] = $v;
        }
    }

    private function tearDownGlobalRequestData()
    {
        $_SERVER = $this->oldServer;
    }

    private function expectedKeyValueData($originalData)
    {
        $data = array();

        foreach ($originalData as $_d) {
            list($expectedKey, $_, $value, $expectedValue) = $_d;

            if ($expectedValue === NULL) {
                $expectedValue = $value;
            }

            $data[] = array($expectedKey, $expectedValue);
        }

        return $data;
    }

    public function requestDataProvider()
    {
        return $this->expectedKeyValueData($this->serverData());
    }

    /**
     * @dataProvider requestDataProvider
     */
    public function testSlowRequestParsing($expectedKey, $expectedValue)
    {
        $this->setUpGlobalRequestData();
        $response = $this->responseData();

        $http = new HTTP(array(
            'forceSlowHeaders' => TRUE,
            'returnResponse' => TRUE,
        ));
        $http->run(
            function ($request) use ($expectedKey, & $actualValue, $response) {
                $actualValue = $request[$expectedKey];
                return $response;
            });

        $this->assertSame($expectedValue, $actualValue);

        $this->tearDownGlobalRequestData();
    }

    public function requestHeaderDataProvider()
    {
        return $this->expectedKeyValueData($this->serverHeaderData());
    }

    /**
     * @dataProvider requestHeaderDataProvider
     */
    public function testSlowRequestHeaderParsing($expectedKey, $expectedValue)
    {
        $this->setUpGlobalRequestData();
        $response = $this->responseData();

        $http = new HTTP(array(
            'forceSlowHeaders' => TRUE,
            'returnResponse' => TRUE,
        ));
        $http->run(
            function ($request) use ($expectedKey, & $actualValue, $response) {
                $actualValue = $request['headers'][$expectedKey];
                return $response;
            });

        $this->assertSame($expectedValue, $actualValue);

        $this->tearDownGlobalRequestData();
    }

    public function requestMagicProvider()
    {
        $expected = array('hello' => 'world');

        return array(
            array('cookies', $expected),
            array('query', $expected),
            array('post',  $expected),
            array('files', $expected),
        );
    }

    private function setUpGlobalMagic()
    {
        $this->oldCookies = $_COOKIE;
        $this->oldGet = $_GET;
        $this->oldPost = $_POST;
        $this->oldFiles = $_FILES;
        
        $_COOKIE = array('a' => 'COOKIE');
        $_GET = array('a' => 'GET', 'b' => 'GET');
        $_POST = array('a' => 'POST', 'c' => 'POST');
        $_FILES = array('a' => 'FILES');
    }

    private function tearDownGlobalMagic()
    {
        $_COOKIE = $this->oldCookies;
        $_GET = $this->oldGet;
        $_POST = $this->oldPost;
        $_FILES = $this->oldFiles;
    }

    /**
     * @dataProvider requestMagicProvider
     */
    public function testRequestMagicParsing($expectedKey, $expectedValue)
    {
        $this->setUpGlobalMagic();
        $response = $this->responseData();

        $http = new HTTP(array('returnResponse' => TRUE));
        $http->run(
            function ($request) use (& $actualRequest, $response) {
                $actualRequest = $request;
                return $response;
            });

        $this->assertSame($_COOKIE, $actualRequest['headers']['cookies']);
        $this->assertSame($_GET, $actualRequest['query']);
        $this->assertSame($_POST, $actualRequest['post']);
        $this->assertSame($_FILES, $actualRequest['files']);
        
        $this->tearDownGlobalMagic();
    }

    public function testRequestParamsParsing()
    {
        $this->setUpGlobalMagic();
        $response = $this->responseData();

        $http = new HTTP(array('returnResponse' => TRUE));
        $http->run(
            function ($request) use (& $actualParams, $response) {
                $actualParams = $request['params'];
                return $response;
            });
        
        $this->assertSame($_POST + $_GET, $actualParams);
        
        $this->tearDownGlobalMagic();
    }

    /**
     * @runInSeparateProcess
     */
    public function testResponseBodySent()
    {
        $expectedResponse = $this->responseData();

        ob_start();

        $http = new HTTP;
        $http->run(
            function ($request) use ($expectedResponse) {
                return $expectedResponse;
            });

        $this->assertSame($expectedResponse['body'], ob_get_clean());
    }

    /**
     * Although we can't test `header()` calls we can force
     * `HTTP::run()` to return the normalised response.
     */
    public function testResponseDefaultHeadersAdded()
    {
        $responseData = $this->responseData();
        $responseData['headers'] = array(
            'Set-Cookie' => array(
                array('name' => 'a', 'value' => '1'),
            ),
        );

        $http = new HTTP(array('returnResponse' => TRUE));
        $actualResponse =
            $http->run(
                function ($request) use ($responseData) {
                    return $responseData;
                });

        $expectedResponse = $responseData;
        $expectedResponse['headers'] += array(
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($expectedResponse['body']),
        );

        $this->assertSame($expectedResponse, $actualResponse);
    }
}
