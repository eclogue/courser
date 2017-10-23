<?php
/**
 * Courser Framework (http://Courserframework.com)
 *
 * @link      https://github.com/Courserphp/Courser
 * @copyright Copyright (c) 2011-2016 mulberry10 Lockhart
 * @license   https://github.com/Courserphp/Courser/blob/master/LICENSE.md (MIT License)
 */
namespace Courser\Tests\Http;

use InvalidArgumentException;
use Courser\Http\Uri;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    /**
     * @var resource
     */
    protected $uri;

    public function uriFactory()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        return new Uri($server);
    }

    /********************************************************************************
     * Scheme
     *******************************************************************************/

    public function testGetScheme()
    {
        $this->assertEquals('https', $this->uriFactory()->getScheme());
    }

    public function testWithScheme()
    {
        $uri = $this->uriFactory()->withScheme('http');

        $this->assertAttributeEquals('http', 'scheme', $uri);
    }

    public function testWithSchemeRemovesSuffix()
    {
        $uri = $this->uriFactory()->withScheme('http://');

        $this->assertAttributeEquals('http', 'scheme', $uri);
    }

    public function testWithSchemeEmpty()
    {
        $uri = $this->uriFactory()->withScheme('');

        $this->assertAttributeEquals('', 'scheme', $uri);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri scheme must be one of: "", "https", "http"
     */
    public function testWithSchemeInvalid()
    {
        $this->uriFactory()->withScheme('ftp');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri scheme must be a string
     */
    public function testWithSchemeInvalidType()
    {
        $this->uriFactory()->withScheme([]);
    }

    /********************************************************************************
     * Authority
     *******************************************************************************/
    public function testGetAuthorityWithUsernameAndPassword()
    {
        $this->assertEquals('mulberry10:123123@example.com', $this->uriFactory()->getAuthority());
    }

    public function testGetAuthorityWithUsername()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);

        $this->assertEquals('mulberry10@example.com', $uri->getAuthority());
    }

    public function testGetAuthority()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);
        $this->assertEquals('example.com', $uri->getAuthority());
    }

    public function testGetAuthorityWithNonStandardPort()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);

        $this->assertEquals('example.com:400', $uri->getAuthority());
    }

    public function testGetUserInfoWithUsernameAndPassword()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);

        $this->assertEquals('mulberry10:123123', $uri->getUserInfo());
    }

    public function testGetUserInfoWithUsername()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);

        $this->assertEquals('mulberry10', $uri->getUserInfo());
    }

    public function testGetUserInfoNone()
    {
        $server = [
            'schema' => 'https',
            'host' => 'test.com',
            'port' => '443',
            'path' => '/test',
            'query' => 'a=1&b=c',
            'fragment' => 'section001',
            'user' => 'mulberry10',
            'password' => '123123',
        ];
        $uri = new Uri($server);

        $this->assertEquals('', $uri->getUserInfo());
    }

    public function testWithUserInfo()
    {
        $uri = $this->uriFactory()->withUserInfo('bob', 'pass');

        $this->assertAttributeEquals('bob', 'user', $uri);
        $this->assertAttributeEquals('pass', 'password', $uri);
    }

    public function testWithUserInfoRemovesPassword()
    {
        $uri = $this->uriFactory()->withUserInfo('bob');

        $this->assertAttributeEquals('bob', 'user', $uri);
        $this->assertAttributeEquals('', 'password', $uri);
    }

    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->uriFactory()->getHost());
    }

    public function testWithHost()
    {
        $uri = $this->uriFactory()->withHost('Courserframework.com');

        $this->assertAttributeEquals('Courserframework.com', 'host', $uri);
    }

    public function testGetPortWithSchemeAndNonDefaultPort()
    {
        $uri = new Uri('https', 'www.example.com', 4000);

        $this->assertEquals(4000, $uri->getPort());
    }

    public function testGetPortWithSchemeAndDefaultPort()
    {
        $uriHttp = new Uri('http', 'www.example.com', 80);
        $uriHttps = new Uri('https', 'www.example.com', 443);

        $this->assertNull($uriHttp->getPort());
        $this->assertNull($uriHttps->getPort());
    }

    public function testGetPortWithoutSchemeAndPort()
    {
        $uri = new Uri('', 'www.example.com');

        $this->assertNull($uri->getPort());
    }

    public function testGetPortWithSchemeWithoutPort()
    {
        $uri = new Uri('http', 'www.example.com');

        $this->assertNull($uri->getPort());
    }

    public function testWithPort()
    {
        $uri = $this->uriFactory()->withPort(8000);

        $this->assertAttributeEquals(8000, 'port', $uri);
    }

    public function testWithPortNull()
    {
        $uri = $this->uriFactory()->withPort(null);

        $this->assertAttributeEquals(null, 'port', $uri);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithPortInvalidInt()
    {
        $this->uriFactory()->withPort(70000);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithPortInvalidString()
    {
        $this->uriFactory()->withPort('Foo');
    }

    /********************************************************************************
     * Path
     *******************************************************************************/

    public function testGetBasePathNone()
    {
        $this->assertEquals('', $this->uriFactory()->getBasePath());
    }

    public function testWithBasePath()
    {
        $uri = $this->uriFactory()->withBasePath('/base');

        $this->assertAttributeEquals('/base', 'basePath', $uri);
    }

    /**
     * @covers Courser\Http\Uri::withBasePath
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri path must be a string
     */
    public function testWithBasePathInvalidType()
    {
        $this->uriFactory()->withBasePath(['foo']);
    }

    public function testWithBasePathAddsPrefix()
    {
        $uri = $this->uriFactory()->withBasePath('base');

        $this->assertAttributeEquals('/base', 'basePath', $uri);
    }

    public function testWithBasePathIgnoresSlash()
    {
        $uri = $this->uriFactory()->withBasePath('/');

        $this->assertAttributeEquals('', 'basePath', $uri);
    }

    public function testGetPath()
    {
        $this->assertEquals('/foo/bar', $this->uriFactory()->getPath());
    }

    public function testWithPath()
    {
        $uri = $this->uriFactory()->withPath('/new');

        $this->assertAttributeEquals('/new', 'path', $uri);
    }

    public function testWithPathWithoutPrefix()
    {
        $uri = $this->uriFactory()->withPath('new');

        $this->assertAttributeEquals('new', 'path', $uri);
    }

    public function testWithPathEmptyValue()
    {
        $uri = $this->uriFactory()->withPath('');

        $this->assertAttributeEquals('', 'path', $uri);
    }

    public function testWithPathUrlEncodesInput()
    {
        $uri = $this->uriFactory()->withPath('/includes?/new');

        $this->assertAttributeEquals('/includes%3F/new', 'path', $uri);
    }

    public function testWithPathDoesNotDoubleEncodeInput()
    {
        $uri = $this->uriFactory()->withPath('/include%25s/new');

        $this->assertAttributeEquals('/include%25s/new', 'path', $uri);
    }

    /**
     * @covers Courser\Http\Uri::withPath
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri path must be a string
     */
    public function testWithPathInvalidType()
    {
        $this->uriFactory()->withPath(['foo']);
    }

    /********************************************************************************
     * Query
     *******************************************************************************/

    public function testGetQuery()
    {
        $this->assertEquals('abc=123', $this->uriFactory()->getQuery());
    }

    public function testWithQuery()
    {
        $uri = $this->uriFactory()->withQuery('xyz=123');

        $this->assertAttributeEquals('xyz=123', 'query', $uri);
    }

    public function testWithQueryRemovesPrefix()
    {
        $uri = $this->uriFactory()->withQuery('?xyz=123');

        $this->assertAttributeEquals('xyz=123', 'query', $uri);
    }

    public function testWithQueryEmpty()
    {
        $uri = $this->uriFactory()->withQuery('');

        $this->assertAttributeEquals('', 'query', $uri);
    }

    /**
     * @covers Courser\Http\Uri::withQuery
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Uri query must be a string
     */
    public function testWithQueryInvalidType()
    {
        $this->uriFactory()->withQuery(['foo']);
    }

    /********************************************************************************
     * Fragment
     *******************************************************************************/
    public function testGetFragment()
    {
        $this->assertEquals('section3', $this->uriFactory()->getFragment());
    }

    public function testWithFragment()
    {
        $uri = $this->uriFactory()->withFragment('other-fragment');

        $this->assertAttributeEquals('other-fragment', 'fragment', $uri);
    }

    public function testWithFragmentRemovesPrefix()
    {
        $uri = $this->uriFactory()->withFragment('#other-fragment');

        $this->assertAttributeEquals('other-fragment', 'fragment', $uri);
    }

    public function testWithFragmentEmpty()
    {
        $uri = $this->uriFactory()->withFragment('');

        $this->assertAttributeEquals('', 'fragment', $uri);
    }

}
