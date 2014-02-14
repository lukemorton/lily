<?php

namespace Lily\Test\Usage;

use Symfony\Component\DomCrawler\Crawler;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Util\Request;
use Lily\Util\Response;

class DescribeTesting extends \PHPUnit_Framework_TestCase
{
    private function applicationToTest()
    {
        $html = file_get_contents(dirname(__FILE__).'/example.html');
        return
            new MiddlewareApplication(
                array(
                    new RoutedApplication(
                        array(
                            array('POST', '/form', $html)))));
    }

    private function applicationResponse($request)
    {
        $application = $this->applicationToTest();
        return $application($request);
    }

    private function htmlHasClass($html, $class)
    {
        $crawler = new Crawler($html);
        return $crawler->filter($class)->count() > 0;
    }

    private function responseBodyHasClass($response, $class)
    {
        return $this->htmlHasClass($response['body'], $class);
    }

    public function testFormErrorShouldBeShown()
    {
        $response = $this->applicationResponse(Request::post('/form'));
        $this->assertTrue($this->responseBodyHasClass($response, '.error'));
    }

    public function testFormShouldSuccessfullySubmit()
    {
        $response = $this->applicationResponse(Request::post('/form'));
        $this->assertTrue($this->responseBodyHasClass($response, 'h1.success'));
    }
}