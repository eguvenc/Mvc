<?php

use Obullo\Mvc\Exception;

class ExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testBadCookieException()
	{
		try {
			throw new Exception\BadCookieException('Test bad cookie exception.');
		} catch (\Exception $e) {
			$this->assertEquals('Test bad cookie exception.', $e->getMessage());
		}
	}

	public function testDefinedServiceException()
	{
		try {
			throw new Exception\DefinedServiceException('Test defined service exception.');
		} catch (\Exception $e) {
			$this->assertEquals('Test defined service exception.', $e->getMessage());
		}
	}

	public function testUndefinedServiceException()
	{
		try {
			throw new Exception\UndefinedServiceException('Test undefined service exception.');
		} catch (\Exception $e) {
			$this->assertEquals('Test undefined service exception.', $e->getMessage());
		}
	}
}