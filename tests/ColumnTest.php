<?php
class FunctionTest extends PHPUnit_Framework_TestCase {

	public function testMysqlReflection() {
		$this->assertTrue(function_exists('array_column'));
	}
}

