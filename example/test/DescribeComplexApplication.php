<?php

namespace Lily\Test\Application;

use Lily\Example\Interaction\Application\MainApplication;

class DescribeComplexApplication extends \PHPUnit_Framework_TestCase
{
    private function application()
    {
        return new MainApplication;
    }

    private function applicationResponse($uri, $request = array())
    {
        return applicationResponse($this->application(), $uri, $request);
    }

    private function applicationFormResponse($uri)
    {
        return applicationFormResponse($this->application(), $uri);
    }

    public function testHomepage()
    {
        $response = $this->applicationResponse('/');
        $this->assertContains('/admin', $response['body']);
    }

    public function testAdminRedirectsToLoginIfNotAuthed()
    {
        $response = $this->applicationResponse('/admin');
        $this->assertContains('Login', $response['body']);
    }

    public function testAdminRedirectsToAdminOnLogin()
    {
        $response = $this->applicationFormResponse('/admin/login');
        $this->assertContains('logout', $response['body']);
    }

    public function testAdminStaysLoggedIn()
    {
        $response = $this->applicationResponse('/admin', authedCookieRequest());
        $this->assertContains('/logout', $response['body']);
    }

    public function testAdminLogsOutSuccessfully()
    {
        $response = $this->applicationResponse('/admin/logout', authedCookieRequest());
        $this->assertContains('Login', $response['body']);
    }

    public function testLoginRedirectsToAdminWhenLoggedIn()
    {
        $response = $this->applicationResponse('/admin/login', authedCookieRequest());
        $this->assertContains('logout', $response['body']);
    }

    public function testCustomNotFoundPage()
    {
        $response = $this->applicationResponse('/doesnt-exist');
        $this->assertSame(404, $response['status']);
        $this->assertContains('We could not find the page you are looking for', $response['body']);
    }
}
