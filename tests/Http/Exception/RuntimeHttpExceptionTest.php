<?php

use Obullo\Http\Exception\RuntimeException;

class RuntimeHttpExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testRuntimeException()
	{
		try {
			throw new RuntimeException('Test runtime exception.');
		} catch (Exception $e) {
			$this->assertEquals('Test runtime exception.', $e->getMessage());
		}
	}
}