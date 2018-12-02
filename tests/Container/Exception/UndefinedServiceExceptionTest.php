<?php

use Obullo\Container\Exception\UndefinedServiceException;

class UndefinedServiceExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testUndefinedServiceException()
	{
		try {
			throw new UndefinedServiceException('Test undefined service exception.');
		} catch (Exception $e) {
			$this->assertEquals('Test undefined service exception.', $e->getMessage());
		}
	}
}