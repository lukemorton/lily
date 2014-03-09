<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Test\Middleware\Session;

abstract class SessionStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldAddArrayToSession()
    {
        $expectedValue = 'some value';
        $response = $this->store()->set(array(), array(
            'session' => array('a' => $expectedValue),
        ));
        $this->assertSame($expectedValue, $this->getFromStore($response, 'a'));
    }

    public function testItShouldOverwriteArrayKeyInSession()
    {
        $expectedValue = 'overwritten value';
        $request = $this->addToStore('b', 'initial value');
        $response = $this->store()->set($request, array(
            'session' => array('b' => $expectedValue),
        ));
        $this->assertSame($expectedValue, $this->getFromStore($response, 'b'));
    }

    public function testItShouldNotOverwriteOtherKeys()
    {
        $expectedValue = 'other key value';
        $request = $this->addToStore('a', $expectedValue);
        $response = $this->store()->set($request, array(
            'session' => array('b' => 'unrelated'),
        ));
        $this->assertSame($expectedValue, $this->getFromStore($response, 'a'));
    }

    public function testItShouldGetArrayFromSession()
    {
        $expectedValue = 'something';
        $request = $this->addToStore('c', $expectedValue);
        $request = $this->store()->get($request);
        $this->assertSame($expectedValue, $request['session']['c']);
    }
}
