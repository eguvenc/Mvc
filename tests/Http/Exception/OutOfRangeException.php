<?php

use Obullo\Http\Exception\OutOfRangeException;

class OutOfRangeExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testRuntimeException()
	{
		try {
			throw new OutOfRangeException('Test out of range exception.');
		} catch (Exception $e) {
			$this->assertEquals('Test out of range exception.', $e->getMessage());
		}
	}
}