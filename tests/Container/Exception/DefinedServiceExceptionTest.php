<?php

use Obullo\Container\Exception\DefinedServiceException;

class DefinedServiceExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testDefinedServiceException()
	{
		try {
			throw new DefinedServiceException('Test defined service exception.');
		} catch (Exception $e) {
			$this->assertEquals('Test defined service exception.', $e->getMessage());
		}
	}
}