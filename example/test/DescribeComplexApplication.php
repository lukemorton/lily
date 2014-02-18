<?php

namespace Lily\Test\Application;

use Lily\Example\Application\MainApplication;

class DescribeComplexApplication extends \PHPUnit_Framework_TestCase
{
    public function testHomepage()
    {
        $response = applicationResponse(new MainApplication, '/');
        $this->assertContains('/admin', $response['body']);
    }

    public function testAdminRedirectsToLoginIfNotAuthed()
    {
        $response = applicationResponse(new MainApplication, '/admin');
        $this->assertContains('Login', $response['body']);
    }

    public function testAdminRedirectsToAdminOnLogin()
    {
        $response = applicationFormResponse(new MainApplication, '/admin/login');
        $this->assertContains('logout', $response['body']);
    }

    public function testAdminStaysLoggedIn()
    {
        $response = applicationResponse(new MainApplication, '/admin', authedCookieRequest());
        $this->assertContains('/logout', $response['body']);
    }

    public function testAdminLogsOutSuccessfully()
    {
        $response = applicationResponse(new MainApplication, '/admin/logout', authedCookieRequest());
        $this->assertContains('Login', $response['body']);
    }

    public function testLoginRedirectsToAdminWhenLoggedIn()
    {
        $response = applicationResponse(new MainApplication, '/admin/login', authedCookieRequest());
        $this->assertContains('logout', $response['body']);
    }

    public function testCustomNotFoundPage()
    {
        $response = applicationResponse(new MainApplication, '/doesnt-exist');
        $this->assertSame(404, $response['status']);
        $this->assertContains('We could not find the page you are looking for', $response['body']);
    }
}
