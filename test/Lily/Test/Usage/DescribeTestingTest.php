<?php

namespace Lily\Test\Usage;

use Symfony\Component\DomCrawler\Crawler;

use Lily\Application\MiddlewareApplication;
use Lily\Application\RoutedApplication;

use Lily\Util\Request;
use Lily\Util\Response;

class DescribeTestingTest extends \PHPUnit_Framework_TestCase
{
    private function crawler($html)
    {
        return new Crawler($html);
    }

    private function applicationToTest()
    {
        $html = file_get_contents(dirname(__FILE__).'/example.html');
        return
            new MiddlewareApplication(
                [new RoutedApplication(
                    [['POST', '/form', $html]])]);
    }

    private function applicationResponse($request)
    {
        $application = $this->applicationToTest();
        return $application($request);
    }

    public function testFormShouldSuccessfullySubmit()
    {
        $response = $this->applicationResponse(Request::post('/form'));
        $crawler = $this->crawler($response['body']);
        $this->assertSame(1, $crawler->filter('h1.success')->count());
    }
}
