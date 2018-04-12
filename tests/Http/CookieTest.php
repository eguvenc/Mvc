<?php

use Obullo\Mvc\Http\Cookie;

class CookieTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $params = [
            'domain' => '.example.com',
            'path'   => '/',
            'secure' => false,
            'httpOnly' => true,
            'expire' => 0
        ];
        $this->cookie = new Cookie;
        $this->cookie->setDefaults($params);
	}

	public function testGetDefaults()
	{
		$defaults = $this->cookie->getDefaults();

		$this->assertEquals($defaults['domain'], '.example.com');
		$this->assertEquals($defaults['path'], '/');
		$this->assertFalse($defaults['secure']);
		$this->assertTrue($defaults['httpOnly']);
		$this->assertEquals($defaults['expire'], 0);
	}

	public function testSetMethodDefaultValues()
	{
		$this->cookie->set('test', 'test_value');
		$responseCookies = $this->cookie->toArray();

		$this->assertEquals('test_value', $responseCookies['test']['value']);
		$this->assertEquals(0, $responseCookies['test']['expire']);
		$this->assertEquals('.example.com', $responseCookies['test']['domain']);
		$this->assertEquals('/', $responseCookies['test']['path']);
		$this->assertFalse($responseCookies['test']['secure']);
		$this->assertTrue($responseCookies['test']['httpOnly']);
	}

	public function testMethodChainingValues()
	{
		$this->cookie
			->name('test')
			->value('value')
			->expire(3600)
			->domain('.test.com')
			->path('/home')
			->secure(true)
			->httpOnly(false);
		$responseCookies = $this->cookie->toArray();

		$this->assertEquals('value', $responseCookies['test']['value']);
		$this->assertEquals(time() + 3600, $responseCookies['test']['expire']);
		$this->assertEquals('.test.com', $responseCookies['test']['domain']);
		$this->assertEquals('/home', $responseCookies['test']['path']);
		$this->assertTrue($responseCookies['test']['secure']);
		$this->assertFalse($responseCookies['test']['httpOnly']);
	}

	public function testGet()
	{
		$params = [
            'domain' => '.example.com',
            'path'   => '/',
            'secure' => false,
            'httpOnly' => true,
            'expire' => 0
        ];
        $this->cookie = new Cookie(['test' => 'value']);
        $this->cookie->setDefaults($params);
        $this->assertEquals('value', $this->cookie->get('test'));
	}

	public function testToHeaders()
	{
		$this->cookie
			->name('test')
			->value('value')
			->expire(3600)
			->domain('.test.com')
			->path('/home')
			->secure(true)
			->httpOnly(false);

		$responseCookies = $this->cookie->toHeaders();
		$expire = '; expires=' . gmdate('D, d-M-Y H:i:s e', time() + 3600);
		$this->assertEquals($responseCookies[0], 'test=value; domain=.test.com; path=/home'.$expire.'; secure');
	}

	public function testToDelete()
	{
		$this->cookie->delete('test');
		$responseCookies = $this->cookie->toArray();

		$this->assertEquals($responseCookies['test']['value'], null);
		$this->assertEquals($responseCookies['test']['expire'], time() + (-1));
	}

}