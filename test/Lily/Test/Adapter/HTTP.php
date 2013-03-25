<?php

namespace Lily\Test\Adapter;

use Lily\Adapter\HTTP;
use Lily\Mock\Application;

class HTTPTest extends \PHPUnit_Framework_TestCase
{
	private $oldServer;
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
			array('method',      'REQUEST_METHOD',  'GET',       'get'),
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
		return array(
			'status' => 200,
			'headers' => array('content-type' => 'text/plain'),
			'body' => 'test this',
		);
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

		$http = new HTTP(array(
			'forceSlowHeaders' => TRUE,
			'returnResponse' => TRUE,
		));
		$http->run(
			new Application(
				function ($request) use ($expectedKey, $expectedValue) {
					$this->assertSame($expectedValue, $request[$expectedKey]);
					return $this->responseData();
				}));

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

		$http = new HTTP(array(
			'forceSlowHeaders' => TRUE,
			'returnResponse' => TRUE,
		));
		$http->run(
			new Application(
				function ($request) use ($expectedKey, $expectedValue) {
					$this->assertSame(
						$expectedValue,
						$request['headers'][$expectedKey]);
					return $this->responseData();
				}));

		$this->tearDownGlobalRequestData();
	}

	public function requestMagicProvider()
	{
		$expected = array('hello' => 'world');

		return array(
			array('query', $expected),
			array('post',  $expected),
			array('files', $expected),
		);
	}

	private function setUpGlobalMagic()
	{
		$this->oldGet = $_GET;
		$this->oldPost = $_POST;
		$this->oldFiles = $_FILES;
		
		$_GET = array('a' => 'GET', 'b' => 'GET');
		$_POST = array('a' => 'POST', 'c' => 'POST');
		$_FILES = array('a' => 'FILES');
	}

	private function tearDownGlobalMagic()
	{
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

		$http = new HTTP(array('returnResponse' => TRUE));
		$http->run(
			new Application(
				function ($request) {
					$this->assertSame($_GET, $request['query']);
					$this->assertSame($_POST, $request['post']);
					$this->assertSame($_FILES, $request['files']);
					return $this->responseData();
				}));
		
		$this->tearDownGlobalMagic();
	}

	public function testRequestParamsParsing()
	{
		$this->setUpGlobalMagic();

		$http = new HTTP(array('returnResponse' => TRUE));
		$http->run(
			new Application(
				function ($request) {
					$this->assertSame($_POST + $_GET, $request['params']);
					return $this->responseData();
				}));
		
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
			new Application(
				function ($request) use ($expectedResponse) {
					return $expectedResponse;
				}));

		$this->assertSame($expectedResponse['body'], ob_get_contents());
		ob_clean();
	}

	/**
	 * Although we can't test `header()` calls we can force
	 * `HTTP::run()` to return the normalised response.
	 */
	public function testResponseDefaultHeadersAdded()
	{
		$responseData = $this->responseData();
		$responseData['headers'] = array();

		$http = new HTTP(array('returnResponse' => TRUE));
		$actualResponse = $http->run(
			new Application(
				function ($request) use ($responseData) {
					return $responseData;
				}));

		$expectedResponse = $responseData;
		$expectedResponse['headers'] = array(
			'content-type' => 'text/html',
			'content-length' => strlen($expectedResponse['body']),
		);

		$this->assertSame($expectedResponse, $actualResponse);
	}

	public function testStringResponseNormalised()
	{
		$expectedResponse = array(
			'status' => 200,
			'headers' => array(
				'content-type' => 'text/html',
				'content-length' => strlen('hey world'),
			),
			'body' => 'hey world',
		);

		$http = new HTTP(array('returnResponse' => TRUE));
		$actualResponse = $http->run(
			new Application(
				function ($request) use ($expectedResponse) {
					return $expectedResponse['body'];
				}));

		$this->assertSame($expectedResponse, $actualResponse);
	}

	public function testNumericArrayResponseNormalised()
	{
		$expectedResponse = array(
			'status' => 200,
			'headers' => array(
				'content-type' => 'text/html',
				'content-length' => strlen('hey world'),
			),
			'body' => 'hey world',
		);

		$http = new HTTP(array('returnResponse' => TRUE));
		$actualResponse = $http->run(
			new Application(
				function ($request) use ($expectedResponse) {
					return array(
						$expectedResponse['status'],
						$expectedResponse['headers'],
						$expectedResponse['body'],
					);
				}));

		$this->assertSame($expectedResponse, $actualResponse);
	}
}
