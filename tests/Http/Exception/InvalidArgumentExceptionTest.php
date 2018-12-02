<?php

use Obullo\Http\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testRuntimeException()
	{
		try {
			throw new InvalidArgumentException('Test invalid exception.');
		} catch (Exception $e) {
			$this->assertEquals('Test invalid exception.', $e->getMessage());
		}
	}
}